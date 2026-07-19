<?php

namespace App\Http\Middleware;

use App\Services\CareerTalentApiClient;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiCompany
{
    public function __construct(private readonly CareerTalentApiClient $api) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->attributes->get('auth.user', []);
        if (($user['role'] ?? null) !== 'company' || ($user['is_admin'] ?? false) === true) {
            $request->session()->forget(['company_auth', 'company']);

            return redirect()->route('company.login')->withErrors([
                'email' => __('marketing.auth.company_required'),
            ]);
        }

        $context = $this->api->companyContext();
        $memberships = is_array($context['body']['memberships'] ?? null) ? $context['body']['memberships'] : [];
        if (! $context['ok'] || $memberships === []) {
            $request->session()->forget(['company_auth', 'company']);

            return redirect()->route('company.login')->withErrors([
                'email' => __('marketing.auth.company_membership_required'),
            ]);
        }

        $activeId = (string) $request->session()->get('company.organization_id', '');
        $active = collect($memberships)->firstWhere('organization_id', $activeId) ?? $memberships[0];
        $request->session()->put('company.organization_id', $active['organization_id']);
        $request->session()->put('company.memberships', $memberships);
        $request->attributes->set('company.membership', $active);

        return $next($request);
    }
}
