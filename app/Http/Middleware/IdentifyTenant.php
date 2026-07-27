<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Filament\Facades\Filament;

class IdentifyTenant
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();

            if (isset($user->company_id) && $user->company_id) {
                // Keep session updated for non-Filament scoped queries
                session(['current_company_id' => $user->company_id]);

                // If a Filament tenant is resolved, double-check that it matches the user's company
                $tenant = Filament::getTenant();
                if ($tenant && (int) $tenant->id !== (int) $user->company_id) {
                    abort(403, 'Unauthorized access to this company.');
                }
            }
        }

        return $next($request);
    }
}
