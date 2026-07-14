<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CareerTalentApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * @var array<string, array{route: string, icon: string}>
     */
    private const MODULE_KEYS = [
        'students' => ['route' => 'admin.students', 'icon' => 'profile'],
        'readiness' => ['route' => 'admin.readiness', 'icon' => 'ladder'],
        'skill-passport' => ['route' => 'admin.skill-passport', 'icon' => 'passport'],
        'job-radar' => ['route' => 'admin.job-radar', 'icon' => 'radar'],
        'applications' => ['route' => 'admin.applications', 'icon' => 'applications'],
        'interviews' => ['route' => 'admin.interviews', 'icon' => 'interview'],
    ];

    /**
     * @var array<string, array{label: string}>
     */
    private const CAREER_DATA_TABS = [
        'roles' => ['label' => 'career-data.tabs.roles'],
        'skills' => ['label' => 'career-data.tabs.skills'],
        'requirements' => ['label' => 'career-data.tabs.requirements'],
        'sources' => ['label' => 'career-data.tabs.sources'],
    ];

    public function dashboard(CareerTalentApiClient $api)
    {
        $response = $api->adminDashboard();
        $data = is_array($response['body']) ? $response['body'] : [];

        return $this->adminView('admin.dashboard', [
            'stats' => $data['stats'] ?? [],
            'recentStudents' => $data['recent_students'] ?? [],
            'modules' => $this->modules($data['module_counts'] ?? []),
            'adminError' => $response['ok'] ? null : $this->apiError($response['error']),
        ], $api);
    }

    public function students(CareerTalentApiClient $api) { return $this->page('students', $api); }
    public function readiness(CareerTalentApiClient $api) { return $this->page('readiness', $api); }
    public function skillPassport(CareerTalentApiClient $api) { return $this->page('skill-passport', $api); }
    public function jobRadar(CareerTalentApiClient $api) { return $this->page('job-radar', $api); }
    public function applications(CareerTalentApiClient $api) { return $this->page('applications', $api); }
    public function interviews(CareerTalentApiClient $api) { return $this->page('interviews', $api); }

    public function careerData(Request $request, CareerTalentApiClient $api): View
    {
        $tab = $request->string('tab', 'roles')->toString();
        abort_unless(isset(self::CAREER_DATA_TABS[$tab]), 404);

        $responses = [];
        foreach (array_keys(self::CAREER_DATA_TABS) as $resource) {
            $responses[$resource] = $api->adminCareerData($resource);
        }
        $failed = array_filter($responses, fn (array $response): bool => ! $response['ok']);

        return $this->adminView('admin.career-data', [
            'activeTab' => $tab,
            'tabs' => array_map(
                fn (string $key): array => ['key' => $key, 'label' => __(self::CAREER_DATA_TABS[$key]['label'])],
                array_keys(self::CAREER_DATA_TABS),
            ),
            'records' => array_map(
                fn (array $response): array => $response['ok'] && is_array($response['body']) ? $response['body'] : [],
                $responses,
            ),
            'adminError' => $failed ? $this->apiError(reset($failed)['error']) : null,
        ], $api);
    }

    public function storeCareerData(Request $request, CareerTalentApiClient $api, string $resource): RedirectResponse
    {
        $this->assertCareerDataResource($resource);
        $response = $api->createAdminCareerData($resource, $this->careerDataPayload($request, $resource));

        return $this->careerDataRedirect($resource, $response['ok'] ? null : $this->apiError($response['error']), $request);
    }

    public function updateCareerData(Request $request, CareerTalentApiClient $api, string $resource, int $record): RedirectResponse
    {
        $this->assertCareerDataResource($resource);
        $response = $api->updateAdminCareerData($resource, $record, $this->careerDataPayload($request, $resource));

        return $this->careerDataRedirect($resource, $response['ok'] ? null : $this->apiError($response['error']), $request);
    }

    public function destroyCareerData(Request $request, CareerTalentApiClient $api, string $resource, int $record): RedirectResponse
    {
        $this->assertCareerDataResource($resource);
        $response = $api->deleteAdminCareerData($resource, $record);

        return $this->careerDataRedirect($resource, $response['ok'] ? null : $this->apiError($response['error']), $request);
    }

    private function page(string $key, CareerTalentApiClient $api)
    {
        abort_unless(isset(self::MODULE_KEYS[$key]), 404);

        $module = $this->moduleDefinition($key);
        $response = $api->adminModule($key);
        $data = is_array($response['body']) ? $response['body'] : [];

        return $this->adminView('admin.page', [
            'page' => [
                'title' => $data['title'] ?? $module['title'],
                'subtitle' => $data['subtitle'] ?? $module['description'],
                'total' => $data['total'] ?? 0,
                'rows' => $data['rows'] ?? [],
            ],
            'adminError' => $response['ok'] ? null : $this->apiError($response['error']),
        ], $api);
    }

    /**
     * @param  mixed  $counts
     * @return list<array{key: string, title: string, description: string, route: string, icon: string, count: int}>
     */
    private function modules(mixed $counts): array
    {
        $counts = is_array($counts) ? $counts : [];

        return array_map(function (string $key) use ($counts): array {
            $module = $this->moduleDefinition($key);

            return [
                'key' => $key,
                ...$module,
                'count' => is_int($counts[$key] ?? null) ? $counts[$key] : 0,
            ];
        }, array_keys(self::MODULE_KEYS));
    }

    /**
     * @return array{title: string, description: string, route: string, icon: string}
     */
    private function moduleDefinition(string $key): array
    {
        $meta = self::MODULE_KEYS[$key];

        return [
            'title' => __("admin.modules.{$key}.title"),
            'description' => __("admin.modules.{$key}.description"),
            'route' => $meta['route'],
            'icon' => $meta['icon'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function adminView(string $view, array $data, CareerTalentApiClient $api)
    {
        return view($view, array_merge($data, [
            'apiHealth' => $api->health(),
            'adminNav' => $this->adminNav(),
        ]));
    }

    /**
     * @return list<array{route: string, label: string, icon: string}>
     */
    private function adminNav(): array
    {
        return [
            ['route' => 'admin.dashboard', 'label' => __('admin.nav.dashboard'), 'icon' => 'dashboard'],
            ['route' => 'admin.career-data', 'label' => __('career-data.title'), 'icon' => 'radar'],
            ...array_map(
                fn (string $key): array => [
                    'route' => self::MODULE_KEYS[$key]['route'],
                    'label' => __("admin.modules.{$key}.title"),
                    'icon' => self::MODULE_KEYS[$key]['icon'],
                ],
                array_keys(self::MODULE_KEYS),
            ),
        ];
    }

    private function apiError(?string $error): string
    {
        return $error
            ? __('admin.errors.api_unavailable', ['error' => $error])
            : __('admin.errors.api_unavailable_generic');
    }

    private function assertCareerDataResource(string $resource): void
    {
        abort_unless(isset(self::CAREER_DATA_TABS[$resource]), 404);
    }

    /**
     * @return array<string, mixed>
     */
    private function careerDataPayload(Request $request, string $resource): array
    {
        return match ($resource) {
            'roles' => $request->validate([
                'slug' => ['required', 'string', 'min:2', 'max:100'],
                'title' => ['required', 'string', 'min:2', 'max:255'],
                'description' => ['nullable', 'string', 'max:10000'],
                'weeks_template' => ['required', 'integer', 'min:1', 'max:104'],
            ]),
            'skills' => array_merge($request->validate([
                'slug' => ['required', 'string', 'min:2', 'max:120'],
                'name' => ['required', 'string', 'min:2', 'max:160'],
                'skill_type' => ['required', 'in:technical,domain,soft,tool'],
                'description' => ['nullable', 'string', 'max:10000'],
            ]), ['is_active' => $request->boolean('is_active')]),
            'sources' => $request->validate([
                'slug' => ['required', 'string', 'min:2', 'max:120'],
                'name' => ['required', 'string', 'min:2', 'max:180'],
                'source_type' => ['required', 'in:official,report,market,manual'],
                'url' => ['nullable', 'string', 'max:2048'],
                'reference_uri' => ['nullable', 'string', 'max:1024'],
                'version' => ['nullable', 'string', 'max:80'],
                'checksum_sha256' => ['nullable', 'string', 'size:64'],
                'license' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:10000'],
                'status' => ['required', 'in:active,archived'],
            ]),
            'requirements' => $request->validate([
                'career_role_id' => ['required', 'integer', 'min:1'],
                'career_skill_id' => ['required', 'integer', 'min:1'],
                'data_source_id' => ['nullable', 'integer', 'min:1'],
                'requirement_type' => ['required', 'in:required,preferred'],
                'expected_level' => ['required', 'in:basic,intermediate,advanced,expert'],
                'weight' => ['required', 'integer', 'min:1', 'max:100'],
                'notes' => ['nullable', 'string', 'max:10000'],
            ]),
        };
    }

    private function careerDataRedirect(string $resource, ?string $error, Request $request): RedirectResponse
    {
        $redirect = redirect()->route('admin.career-data', ['tab' => $resource]);

        return $error ? $redirect->withErrors(['career_data' => $error])->withInput() : $redirect;
    }
}
