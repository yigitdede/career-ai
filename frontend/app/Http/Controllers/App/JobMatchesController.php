<?php

namespace App\Http\Controllers\App;

use App\Data\PanelDemoData;
use App\Data\PanelJobMatchDemoData;
use App\Services\CareerTalentApiClient;
use App\Services\JobMatchAnalyzer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobMatchesController extends PanelController
{
    public function show(CareerTalentApiClient $api)
    {
        $result = $api->panel('job-matches');
        $fallback = $this->fallbackJobMatches();
        $data = $fallback;
        if ($result['ok'] && is_array($result['body'])) {
            $data = array_merge($fallback, $result['body']);
        }

        return $this->panelView('app.job-matches', [
            'seedJobs' => $data['seed_jobs'],
            'userSkills' => $data['user_skills'],
            'readiness' => $data['readiness'],
        ]);
    }

    public function analyze(Request $request, CareerTalentApiClient $api): JsonResponse
    {
        $validated = $request->validate([
            'url' => ['required', 'string', 'max:2048'],
        ]);

        $result = $api->analyzePanelJob($validated['url']);
        if (! ($result['ok'] ?? false)) {
            return response()->json([
                'message' => $result['error'] ?? __('panel.job_matches.error_generic'),
            ], $result['status'] ?? 502);
        }

        return response()->json(['job' => $result['body']['job'] ?? null]);
    }

    /**
     * @return array{seed_jobs: list<array<string, mixed>>, user_skills: list<string>, readiness: int}
     */
    private function fallbackJobMatches(): array
    {
        $analyzer = new JobMatchAnalyzer(
            PanelJobMatchDemoData::userSkills(),
            PanelDemoData::stats()['readiness'],
        );

        return [
            'seed_jobs' => array_map(
                fn (string $url) => $analyzer->analyze($url),
                PanelJobMatchDemoData::seedUrls(),
            ),
            'user_skills' => PanelJobMatchDemoData::userSkills(),
            'readiness' => PanelDemoData::stats()['readiness'],
        ];
    }
}
