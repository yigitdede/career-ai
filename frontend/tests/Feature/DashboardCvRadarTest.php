<?php

namespace Tests\Feature;

use Tests\TestCase;

class DashboardCvRadarTest extends TestCase
{
    public function test_dashboard_shows_empty_state_without_cv_analysis(): void
    {
        $response = $this->get(route('panel.dashboard'));

        $response->assertOk()
            ->assertSee(__('panel.skill_radar.empty_title'), false)
            ->assertDontSee('id="yetenek-radari"', false);
    }

    public function test_dashboard_shows_session_radar_after_cv_analysis(): void
    {
        session([
            'cv_analysis' => [
                'file_name' => 'Buse_Batan.pdf',
                'skill_radar' => [
                    'overall_match' => 68,
                    'analyzed_at' => '4 Jul 2026',
                    'target_role' => 'İş Analisti',
                    'skills' => [
                        ['label' => 'Excel', 'score' => 82, 'target' => 70],
                        ['label' => 'İletişim', 'score' => 90, 'target' => 90],
                    ],
                ],
                'career_ladder' => [
                    [
                        'id' => 'business-analyst',
                        'title' => 'İş Analisti',
                        'readiness' => 68,
                    ],
                ],
            ],
        ]);

        $response = $this->get(route('panel.dashboard'));

        $response->assertOk()
            ->assertSee('id="yetenek-radari"', false)
            ->assertSee('İş Analisti', false)
            ->assertSee('Buse_Batan.pdf', false)
            ->assertSee('%68', false)
            ->assertSee(__('panel.skill_radar.from_cv_analysis'), false);
    }
}
