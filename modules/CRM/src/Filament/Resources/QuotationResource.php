<?php

namespace Modules\CRM\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\CRM\Models\Quotation;
use Modules\CRM\Filament\Resources\QuotationResource\Pages;

class QuotationResource extends Resource
{
    protected static ?string $model = Quotation::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'CRM';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('quotation_number')
                            ->required()
                            ->maxLength(255)
                            ->default(fn () => 'QT-' . strtoupper(uniqid())),

                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'sent' => 'Sent',
                                'accepted' => 'Accepted',
                                'rejected' => 'Rejected',
                                'expired' => 'Expired',
                            ])
                            ->required()
                            ->default('draft'),

                        Forms\Components\Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Forms\Components\Select::make('lead_id')
                            ->relationship('lead', 'title')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Forms\Components\Select::make('assigned_user_id')
                            ->relationship('assignedUser', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('total_amount')
                            ->numeric()
                            ->prefix('$')
                            ->default(0.00)
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Quotation Items')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Forms\Components\TextInput::make('description')
                                    ->required()
                                    ->columnSpan(4),

                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, callable $set, callable $get) => static::calculateItemTotal($set, $get))
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('unit_price')
                                    ->numeric()
                                    ->required()
                                    ->prefix('$')
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, callable $set, callable $get) => static::calculateItemTotal($set, $get))
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('discount')
                                    ->numeric()
                                    ->default(0.00)
                                    ->prefix('$')
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, callable $set, callable $get) => static::calculateItemTotal($set, $get))
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('tax_rate')
                                    ->numeric()
                                    ->label('Tax %')
                                    ->default(0.00)
                                    ->suffix('%')
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, callable $set, callable $get) => static::calculateItemTotal($set, $get))
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('total')
                                    ->numeric()
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated()
                                    ->required()
                                    ->columnSpan(2),
                            ])
                            ->columns(12)
                            ->columnSpanFull()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, callable $get) {
                                $items = $get('items') ?? [];
                                $grandTotal = 0.00;
                                foreach ($items as $item) {
                                    $quantity = floatval($item['quantity'] ?? 1);
                                    $unitPrice = floatval($item['unit_price'] ?? 0);
                                    $discount = floatval($item['discount'] ?? 0);
                                    $taxRate = floatval($item['tax_rate'] ?? 0);
                                    $grandTotal += ($quantity * $unitPrice - $discount) * (1 + $taxRate / 100);
                                }
                                $set('total_amount', round($grandTotal, 2));
                            })
                            ->createItemButtonLabel('Add Line Item'),
                    ])
            ]);
    }

    public static function calculateItemTotal(callable $set, callable $get): void
    {
        $quantity = floatval($get('quantity') ?? 1);
        $unitPrice = floatval($get('unit_price') ?? 0);
        $discount = floatval($get('discount') ?? 0);
        $taxRate = floatval($get('tax_rate') ?? 0);

        $subtotal = $quantity * $unitPrice;
        $total = ($subtotal - $discount) * (1 + $taxRate / 100);

        $set('total', round($total, 2));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('quotation_number')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->sortable()
                    ->searchable()
                    ->placeholder('No Customer'),

                Tables\Columns\TextColumn::make('lead.title')
                    ->sortable()
                    ->searchable()
                    ->placeholder('No Lead'),

                Tables\Columns\TextColumn::make('total_amount')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        'sent' => 'info',
                        'draft' => 'warning',
                        'expired' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('assignedUser.name')
                    ->sortable()
                    ->label('Assigned To'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'sent' => 'Sent',
                        'accepted' => 'Accepted',
                        'rejected' => 'Rejected',
                        'expired' => 'Expired',
                    ]),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('downloadPdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn (Quotation $record): string => route('quotation.pdf', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuotations::route('/'),
            'create' => Pages\CreateQuotation::route('/create'),
            'edit' => Pages\EditQuotation::route('/{record}/edit'),
        ];
    }
}
