<?php

use App\Models\Company;
use Filament\Facades\Filament;

if (! function_exists('current_company')) {
    function current_company(): ?Company
    {
        // 1. Resolve from Filament tenant context if active
        if (class_exists(Filament::class) && Filament::getTenant()) {
            $tenant = Filament::getTenant();
            if ($tenant instanceof Company) {
                return $tenant;
            }
        }

        // 2. Fallback to authenticated user's company_id
        if (auth()->check()) {
            $user = auth()->user();
            if (isset($user->company_id) && $user->company_id) {
                return Company::find($user->company_id);
            }
        }

        // 3. Fallback to session
        if (session()->has('current_company_id')) {
            return Company::find(session()->get('current_company_id'));
        }

        return null;
    }
}

if (! function_exists('current_company_id')) {
    function current_company_id(): ?int
    {
        // 1. Resolve from Filament tenant context if active
        if (class_exists(Filament::class) && Filament::getTenant()) {
            return Filament::getTenant()->id;
        }

        // 2. Fallback to authenticated user's company_id
        if (auth()->check()) {
            $user = auth()->user();
            if (isset($user->company_id) && $user->company_id) {
                return (int) $user->company_id;
            }
        }

        // 3. Fallback to session
        if (session()->has('current_company_id')) {
            return (int) session()->get('current_company_id');
        }

        return null;
    }
}
