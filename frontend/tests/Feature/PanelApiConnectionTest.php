<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PanelApiConnectionTest extends TestCase
{
    public function test_dashboard_uses_fastapi_panel_payload(): void
    {
        Http::fake([
            'http://localhost:8000/health' => Http::response(['status' => 'ok'], 200),
            'http://localhost:8000/api/v1/panel/dashboard' => Http::response([
                'stats' => [
                    'readiness' => 91,
                    'career' => 'API Panel Analisti',
                    'weekly_tasks_total' => 1,
                    'weekly_tasks_done' => 0,
                ],
                'weekly_tasks' => [[
                    'id' => 'api-task-1',
                    'title' => 'API görevini tamamla',
                    'hint' => 'FastAPI panel verisi',
                    'done' => false,
                ]],
                'learning_resources' => [[
                    'id' => 'api-course-1',
                    'title' => 'API Kaynak Kursu',
                    'provider' => 'FastAPI Academy',
                    'price_type' => 'free',
                    'price_label' => 'Ücretsiz',
                    'price_range' => '0-500',
                    'has_certificate' => true,
                    'skills' => ['API'],
                    'url' => 'https://example.com/api-course',
                ]],
            ], 200),
            'http://localhost:8000/*' => Http::response([], 200),
        ]);

        $response = $this->get('/panel');

        $response->assertOk();
        $response->assertSee('API Panel Analisti', false);
        $response->assertSee('API görevini tamamla', false);
        $response->assertSee('API Kaynak Kursu', false);
        Http::assertSent(fn ($request) => $request->url() === 'http://localhost:8000/api/v1/panel/dashboard');
    }

    public function test_panel_feature_pages_use_fastapi_payloads(): void
    {
        Http::fake([
            'http://localhost:8000/health' => Http::response(['status' => 'ok'], 200),
            'http://localhost:8000/api/v1/panel/job-radar' => Http::response([
                'radar' => [
                    'roles' => ['API Growth Analyst'],
                    'sources' => ['FastAPI Jobs'],
                    'alerts' => [[
                        'company' => 'API Radar Co',
                        'role' => 'API Growth Analyst',
                        'source' => 'FastAPI Jobs',
                        'match' => 88,
                        'salary' => '₺55k-70k',
                        'gaps' => ['dbt'],
                        'action' => 'API radar aksiyonu',
                    ]],
                ],
            ], 200),
            'http://localhost:8000/api/v1/panel/skill-passport' => Http::response([
                'passport' => [
                    'score' => 84,
                    'verified' => 1,
                    'total' => 1,
                    'items' => [[
                        'skill' => 'API Kanıt Yeteneği',
                        'level' => 'İleri',
                        'evidence' => 'FastAPI kanıt kartı',
                        'type' => 'API',
                        'status' => 'verified',
                        'impact' => 'Canlı API verisi',
                    ]],
                    'gaps' => ['API kanıt boşluğu'],
                ],
            ], 200),
            'http://localhost:8000/api/v1/panel/chat' => Http::response([
                'assistant' => [
                    'prompts' => [[
                        'q' => 'API asistan mesajı',
                        'a' => 'API hızlı cevap',
                    ]],
                ],
            ], 200),
            'http://localhost:8000/*' => Http::response([], 200),
        ]);

        $this->get('/panel/is-radari')->assertOk()->assertSee('API Radar Co', false);
        $this->get('/panel/yetenek-pasaportu')->assertOk()->assertSee('API Kanıt Yeteneği', false);
        $this->get('/panel/sohbet')->assertOk()->assertSee('API asistan mesajı', false);

        Http::assertSent(fn ($request) => $request->url() === 'http://localhost:8000/api/v1/panel/job-radar');
        Http::assertSent(fn ($request) => $request->url() === 'http://localhost:8000/api/v1/panel/skill-passport');
        Http::assertSent(fn ($request) => $request->url() === 'http://localhost:8000/api/v1/panel/chat');
    }

    public function test_job_match_analyze_posts_to_fastapi(): void
    {
        Http::fake([
            'http://localhost:8000/api/v1/panel/job-matches/analyze' => Http::response([
                'job' => [
                    'id' => 'api-job-analysis',
                    'title' => 'API İlan Analizi',
                    'company' => 'FastAPI HR',
                    'source' => 'api.example',
                    'url' => 'https://api.example/jobs/1',
                    'match_score' => 93,
                    'matched_skills' => ['SQL'],
                    'missing_skills' => [],
                    'recommendation' => 'apply',
                    'analyzed_at' => '2026-07-07T00:00:00+00:00',
                ],
            ], 200),
        ]);

        $response = $this->postJson('/panel/ilan-eslestirme/analiz', [
            'url' => 'https://api.example/jobs/1',
        ]);

        $response->assertOk();
        $response->assertJsonPath('job.title', 'API İlan Analizi');
        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && $request->url() === 'http://localhost:8000/api/v1/panel/job-matches/analyze');
    }
}
