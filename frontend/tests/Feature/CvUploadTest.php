<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CvUploadTest extends TestCase
{
    public function test_cv_analyze_route_proxies_to_api_and_stores_session(): void
    {
        $this->withoutMiddleware();

        Http::fake([
            'http://localhost:8000/api/v1/cv/analyze' => Http::response([
                'status' => 'ready',
                'skill_radar' => [
                    'overall_match' => 71,
                    'analyzed_at' => '4 Jul 2026',
                    'target_role' => 'Veri Analisti',
                    'skills' => [
                        ['label' => 'SQL', 'score' => 80, 'target' => 90],
                    ],
                ],
                'career_ladder' => [
                    [
                        'id' => 'data-analyst',
                        'tier' => 'near',
                        'tier_label' => 'B — Yakın',
                        'title' => 'Veri Analisti',
                        'readiness' => 61,
                        'gap_count' => 2,
                        'gaps_summary' => 'Python',
                        'weeks_estimate' => '4–8 hafta',
                        'swot' => [
                            'strengths' => ['SQL'],
                            'weaknesses' => ['Python'],
                            'opportunities' => ['Bootcamp'],
                            'threats' => ['Rekabet'],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $pdf = UploadedFile::fake()->create('cv.pdf', 120, 'application/pdf');

        $response = $this->post(route('panel.cv.analyze'), ['cv' => $pdf]);

        $response->assertOk()
            ->assertJsonPath('status', 'ready')
            ->assertJsonPath('career_ladder.0.title', 'Veri Analisti');

        $this->assertNotNull(session('cv_analysis.career_ladder'));
    }
}
