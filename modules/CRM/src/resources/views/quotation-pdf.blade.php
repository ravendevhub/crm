<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quotation {{ $quotation->quotation_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .container {
            padding: 20px;
        }
        .header {
            margin-bottom: 30px;
            border-bottom: 2px solid #f3f4f6;
            padding-bottom: 15px;
        }
        .header table {
            width: 100%;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            color: #1f2937;
        }
        .company-details {
            text-align: right;
            color: #6b7280;
            font-size: 12px;
        }
        .details-table {
            width: 100%;
            margin-bottom: 30px;
            border-collapse: collapse;
        }
        .details-table td {
            width: 50%;
            vertical-align: top;
        }
        .section-title {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #9ca3af;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .info-value {
            font-size: 14px;
            color: #374151;
            font-weight: 500;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            background-color: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            color: #4b5563;
            font-weight: bold;
            text-align: left;
            padding: 10px;
            font-size: 11px;
            text-transform: uppercase;
        }
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
            color: #4b5563;
        }
        .text-right {
            text-align: right !important;
        }
        .summary-container {
            width: 100%;
            margin-top: 20px;
        }
        .summary-table {
            width: 40%;
            float: right;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 6px 10px;
            color: #4b5563;
        }
        .summary-table tr.total-row td {
            border-top: 2px solid #e5e7eb;
            font-size: 16px;
            font-weight: bold;
            color: #111827;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            font-size: 10px;
            font-weight: bold;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .status-draft { background-color: #e5e7eb; color: #374151; }
        .status-sent { background-color: #dbeafe; color: #1e40af; }
        .status-accepted { background-color: #d1fae5; color: #065f46; }
        .status-rejected { background-color: #fee2e2; color: #991b1b; }
        .status-expired { background-color: #fef3c7; color: #92400e; }
        .footer {
            margin-top: 50px;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
            font-size: 11px;
            color: #9ca3af;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <table>
                <tr>
                    <td>
                        <span class="title">QUOTATION</span><br>
                        <span style="color: #6b7280; font-size: 12px;">No: {{ $quotation->quotation_number }}</span>
                    </td>
                    <td class="company-details">
                        <strong>{{ $quotation->company->name }}</strong><br>
                        Generated on {{ now()->format('F d, Y') }}
                    </td>
                </tr>
            </table>
        </div>

        <table class="details-table">
            <tr>
                <td>
                    <div class="section-title">Prepared For</div>
                    <div class="info-value">
                        @if ($quotation->customer)
                            <strong>{{ $quotation->customer->name }}</strong><br>
                            {{ $quotation->customer->company_name }}<br>
                            {{ $quotation->customer->phone }}<br>
                            {{ $quotation->customer->email }}
                        @elseif ($quotation->lead)
                            <strong>{{ $quotation->lead->contact_name }}</strong><br>
                            {{ $quotation->lead->title }}<br>
                            {{ $quotation->lead->phone }}<br>
                            {{ $quotation->lead->email }}
                        @else
                            <em>No Contact Details</em>
                        @endif
                    </div>
                </td>
                <td class="text-right">
                    <div class="section-title">Quotation Details</div>
                    <div style="margin-bottom: 5px;">
                        <strong>Status: </strong>
                        <span class="status-badge status-{{ strtolower($quotation->status) }}">
                            {{ $quotation->status }}
                        </span>
                    </div>
                    <div>
                        <strong>Date: </strong> {{ $quotation->created_at->format('M d, Y') }}
                    </div>
                    @if ($quotation->assignedUser)
                        <div>
                            <strong>Assigned To: </strong> {{ $quotation->assignedUser->name }}
                        </div>
                    @endif
                </td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right" style="width: 10%;">Qty</th>
                    <th class="text-right" style="width: 15%;">Unit Price</th>
                    <th class="text-right" style="width: 15%;">Discount</th>
                    <th class="text-right" style="width: 10%;">Tax Rate</th>
                    <th class="text-right" style="width: 15%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($quotation->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                        <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right">${{ number_format($item->discount, 2) }}</td>
                        <td class="text-right">{{ number_format($item->tax_rate, 2) }}%</td>
                        <td class="text-right">${{ number_format($item->total, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #9ca3af;">No items listed on this quotation.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="summary-container">
            <table class="summary-table">
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-right">${{ number_format($quotation->items->sum(fn ($i) => $i->quantity * $i->unit_price), 2) }}</td>
                </tr>
                <tr>
                    <td>Discount:</td>
                    <td class="text-right">-${{ number_format($quotation->items->sum('discount'), 2) }}</td>
                </tr>
                <tr>
                    <td>Tax:</td>
                    <td class="text-right">${{ number_format($quotation->items->sum(fn ($i) => (($i->quantity * $i->unit_price) - $i->discount) * ($i->tax_rate / 100)), 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td>Total Amount:</td>
                    <td class="text-right">${{ number_format($quotation->total_amount, 2) }}</td>
                </tr>
            </table>
            <div style="clear: both;"></div>
        </div>

        <div class="footer">
            Thank you for your business! If you have any questions, please contact the representative listed above.
        </div>
    </div>
</body>
</html>
