<?php

namespace Tests\Feature;

use Tests\TestCase;

class CvBuilderRadarTest extends TestCase
{
    public function test_cv_builder_hides_demo_radar_without_analysis(): void
    {
        $response = $this->get(route('panel.cv-builder'));

        $response->assertOk()
            ->assertDontSee('id="yetenek-radari"', false);
    }

    public function test_cv_builder_shows_session_radar_after_cv_analysis(): void
    {
        session([
            'cv_analysis' => [
                'file_name' => 'Buse_Batan.pdf',
                'skill_radar' => [
                    'overall_match' => 71,
                    'analyzed_at' => '4 Jul 2026',
                    'target_role' => 'Business Analyst',
                    'skills' => [
                        ['label' => 'Excel', 'score' => 80, 'target' => 70],
                    ],
                ],
                'career_ladder' => [
                    ['id' => 'business-analyst', 'title' => 'Business Analyst', 'readiness' => 71],
                ],
            ],
        ]);

        $response = $this->get(route('panel.cv-builder', ['locale' => 'en']));

        $response->assertOk()
            ->assertSee('id="yetenek-radari"', false)
            ->assertSee('Business Analyst', false)
            ->assertSee('Buse_Batan.pdf', false)
            ->assertSee('%71', false);
    }
}
