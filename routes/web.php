<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/quotations/{quotation}/pdf', function (Modules\CRM\Models\Quotation $quotation) {
    if (!auth()->check()) {
        abort(403);
    }
    
    $user = auth()->user();
    if ($user->company_id !== $quotation->company_id && !$user->hasRole('Super Admin')) {
        abort(403);
    }

    if (!$user->can('view_quotations')) {
        abort(403);
    }

    $quotation->load(['company', 'customer', 'lead', 'items']);
    
    $pdf = Barryvdh\DomPDF\Facade\Pdf::loadView('crm::quotation-pdf', compact('quotation'));
    return $pdf->download("quotation-{$quotation->quotation_number}.pdf");
})->name('quotation.pdf')->middleware(['web', 'auth']);
