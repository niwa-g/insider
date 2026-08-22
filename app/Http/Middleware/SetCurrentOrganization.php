<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentOrganization
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->organization_id) {
            app()->instance('currentOrganizationId', $user->organization_id);
        }

        return $next($request);
    }
}
