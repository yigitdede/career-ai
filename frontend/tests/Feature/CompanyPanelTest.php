<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureApiAuthenticated;
use App\Http\Middleware\EnsureApiAdmin;
use App\Http\Middleware\EnsureApiCandidate;
use App\Http\Middleware\EnsureApiCompany;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CompanyPanelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withMiddleware([
            EnsureApiAuthenticated::class,
            EnsureApiAdmin::class,
            EnsureApiCandidate::class,
            EnsureApiCompany::class,
        ]);
    }

    private function user(array $overrides = []): array
    {
        return array_merge(['id' => 44, 'full_name' => 'Acme Owner', 'email' => 'owner@acme.example.com', 'is_active' => true, 'is_admin' => false, 'role' => 'company', 'preferred_locale' => 'tr'], $overrides);
    }

    private function membership(array $overrides = []): array
    {
        return array_merge([
            'organization_id' => 'org-1', 'organization_name' => 'Acme Teknoloji', 'organization_slug' => 'acme',
            'organization_type' => 'employer', 'organization_status' => 'active', 'plan_code' => 'pilot',
            'billing_email' => 'billing@acme.example.com', 'website' => 'https://acme.example.com',
            'role' => 'owner', 'permissions' => ['dashboard.view', 'organization.update', 'members.view', 'members.invite', 'members.manage'],
        ], $overrides);
    }

    public function test_company_login_accepts_only_company_account_with_membership(): void
    {
        Http::fake([
            '*/api/v1/auth/login' => Http::response(['access_token' => 'company-token', 'token_type' => 'bearer']),
            '*/api/v1/auth/me' => Http::response($this->user()),
            '*/api/v1/company/context' => Http::response(['memberships' => [$this->membership()]]),
        ]);

        $this->withSession(['auth.access_token' => 'admin-token'])
            ->post('/company/login', ['email' => 'owner@acme.example.com', 'password' => 'password'])
            ->assertRedirect('/company')
            ->assertSessionHas('company_auth.access_token', 'company-token')
            ->assertSessionHas('auth.access_token', 'admin-token')
            ->assertSessionHas('company.organization_id', 'org-1');
    }

    public function test_company_dashboard_team_and_profile_render_real_api_contracts(): void
    {
        Http::fake([
            '*/api/v1/auth/me' => Http::response($this->user()),
            '*/api/v1/company/context' => Http::response(['memberships' => [$this->membership()]]),
            '*/api/v1/company/dashboard' => Http::response(['organization' => $this->membership(), 'members_total' => 2, 'members_active' => 1, 'invitations_pending' => 1]),
            '*/api/v1/company/members' => Http::response(['members' => [[
                'membership_id' => 'm1', 'user_id' => 44, 'full_name' => 'Acme Owner', 'email' => 'owner@acme.example.com', 'role' => 'owner', 'status' => 'active', 'created_at' => '2026-07-19T10:00:00Z',
            ]], 'pending_invitations' => []]),
        ]);
        $session = ['company_auth.access_token' => 'company-token', 'company_auth.user' => $this->user()];

        $this->withSession($session)->get('/company')->assertOk()->assertSee('Acme Teknoloji')->assertSee('2');
        $this->withSession($session)->get('/company/ekip')->assertOk()->assertSee('Acme Owner')->assertSee('Ekip ve Yetkiler');
        $this->withSession($session)->get('/company/profil')
            ->assertOk()
            ->assertSee('billing@acme.example.com')
            ->assertSee('action="'.route('company.logout').'"', false);
    }

    public function test_company_panel_uses_its_own_teal_emerald_visual_identity(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));
        $views = implode("\n", array_map(
            static fn (string $path): string => file_get_contents($path),
            [
                resource_path('views/company/layouts/app.blade.php'),
                resource_path('views/company/dashboard.blade.php'),
                resource_path('views/company/team.blade.php'),
                resource_path('views/company/profile.blade.php'),
            ],
        ));

        $this->assertStringContainsString('--company-accent: #0f766e', $css);
        $this->assertStringContainsString('--company-brand: #10b981', $css);
        $this->assertStringContainsString('--company-accent-ink-dark: #5eead4', $css);
        $this->assertStringContainsString('--admin-accent: #ffbd72', $css);
        $this->assertStringContainsString('company-shell', $views);
        $this->assertStringContainsString('company-btn-primary', $views);
        $this->assertStringContainsString('panel-nav-link-active', $views);
        $this->assertStringNotContainsString('admin-shell', $views);
        $this->assertStringNotContainsString('admin-btn-', $views);
    }

    public function test_candidate_cannot_open_company_panel(): void
    {
        Http::fake(['*/api/v1/auth/me' => Http::response(array_merge($this->user(), ['role' => 'student']))]);
        $this->withSession(['auth.access_token' => 'candidate-token'])
            ->get('/company')
            ->assertRedirect(route('company.login'))
            ->assertSessionHas('auth.access_token', 'candidate-token');
    }

    public function test_admin_without_company_session_is_redirected_instead_of_forbidden(): void
    {
        Http::fake(['*/api/v1/auth/me' => Http::response($this->user(['is_admin' => true, 'role' => 'super_admin']))]);

        $this->withSession(['auth.access_token' => 'admin-token'])
            ->get('/company')
            ->assertRedirect(route('company.login'))
            ->assertSessionHas('auth.access_token', 'admin-token');
    }

    public function test_company_account_cannot_open_candidate_panel(): void
    {
        $this->withSession(['company_auth.access_token' => 'company-token'])
            ->get('/panel')
            ->assertRedirect(route('login'))
            ->assertSessionHas('company_auth.access_token', 'company-token');
    }

    public function test_company_can_persist_portal_language(): void
    {
        Http::fake([
            '*/api/v1/auth/me' => Http::response($this->user()),
            '*/api/v1/company/context' => Http::response(['memberships' => [$this->membership()]]),
            '*/api/v1/auth/me/locale' => Http::response(array_merge($this->user(), ['preferred_locale' => 'en'])),
        ]);

        $this->withSession(['company_auth.access_token' => 'company-token'])
            ->from('/company')
            ->get('/company/locale/en')
            ->assertRedirect('/company')
            ->assertSessionHas('panel_locale', 'en')
            ->assertSessionHas('company_auth.user.preferred_locale', 'en');
    }

    public function test_legacy_company_session_is_migrated_without_relogin(): void
    {
        Http::fake([
            '*/api/v1/auth/me' => Http::response($this->user()),
            '*/api/v1/company/context' => Http::response(['memberships' => [$this->membership()]]),
            '*/api/v1/company/dashboard' => Http::response(['organization' => $this->membership(), 'members_total' => 1, 'members_active' => 1, 'invitations_pending' => 0]),
        ]);

        $this->withSession(['auth.access_token' => 'legacy-company-token', 'auth.user' => $this->user()])
            ->get('/company')
            ->assertOk()
            ->assertSessionHas('company_auth.access_token', 'legacy-company-token')
            ->assertSessionMissing('auth.access_token');
    }

    public function test_admin_and_company_sessions_use_their_own_tokens_in_the_same_browser(): void
    {
        $admin = $this->user([
            'id' => 1,
            'full_name' => 'Platform Admin',
            'email' => 'admin@example.com',
            'is_admin' => true,
            'role' => 'super_admin',
            'admin_permissions' => [],
            'must_change_password' => false,
        ]);
        Http::fake(function ($request) use ($admin) {
            if (str_ends_with($request->url(), '/api/v1/auth/login')) {
                return Http::response(['access_token' => 'company-token', 'token_type' => 'bearer']);
            }
            if (str_ends_with($request->url(), '/api/v1/auth/me')) {
                return Http::response($request->hasHeader('Authorization', 'Bearer company-token') ? $this->user() : $admin);
            }
            if (str_ends_with($request->url(), '/api/v1/company/context')) {
                return Http::response(['memberships' => [$this->membership()]]);
            }
            if (str_ends_with($request->url(), '/api/v1/company/dashboard')) {
                return Http::response(['organization' => $this->membership(), 'members_total' => 1, 'members_active' => 1, 'invitations_pending' => 0]);
            }
            if (str_ends_with($request->url(), '/api/v1/admin/dashboard')) {
                return Http::response(['stats' => [], 'module_counts' => [], 'recent_students' => []]);
            }

            return Http::response(['status' => 'ok']);
        });

        $this->withSession(['auth.access_token' => 'admin-token', 'auth.user' => $admin])
            ->post('/company/login', ['email' => 'owner@acme.example.com', 'password' => 'password'])
            ->assertRedirect('/company')
            ->assertSessionHas('auth.access_token', 'admin-token')
            ->assertSessionHas('company_auth.access_token', 'company-token');

        $this->get('/company')->assertOk();
        $this->get('/admin')->assertOk();

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/api/v1/company/dashboard')
            && $request->hasHeader('Authorization', 'Bearer company-token'));
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/api/v1/admin/dashboard')
            && $request->hasHeader('Authorization', 'Bearer admin-token'));
    }

    public function test_company_logout_preserves_admin_session(): void
    {
        $this->withSession([
            'auth.access_token' => 'admin-token',
            'company_auth.access_token' => 'company-token',
            'company.organization_id' => 'org-1',
        ])->post('/company/cikis')
            ->assertRedirect(route('company.login'))
            ->assertSessionHas('auth.access_token', 'admin-token')
            ->assertSessionMissing('company_auth.access_token')
            ->assertSessionMissing('company.organization_id');
    }
}
