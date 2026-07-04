<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class CareerTalentApiClient
{
    public function baseUrl(): string
    {
        return rtrim(config('services.careertalent.api_url'), '/');
    }

    /**
     * @return array{ok: bool, status: ?int, body: ?array<string, mixed>, error: ?string}
     */
    public function health(): array
    {
        try {
            $response = Http::timeout(3)->get($this->baseUrl().'/health');

            return [
                'ok' => $response->successful(),
                'status' => $response->status(),
                'body' => $response->json(),
                'error' => null,
            ];
        } catch (ConnectionException $exception) {
            return [
                'ok' => false,
                'status' => null,
                'body' => null,
                'error' => $exception->getMessage(),
            ];
        }
    }

    /**
     * @return array{ok: bool, status: ?int, body: ?array<string, mixed>, error: ?string}
     */
    public function analyzeCv(UploadedFile $file): array
    {
        try {
            $response = Http::timeout(120)
                ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                ->post($this->baseUrl().'/api/v1/cv/analyze');

            if (! $response->successful()) {
                $message = $response->json('detail')
                    ?? $response->json('message')
                    ?? $response->body();

                return [
                    'ok' => false,
                    'status' => $response->status(),
                    'body' => null,
                    'error' => is_string($message) ? $message : 'CV analizi başarısız',
                ];
            }

            return [
                'ok' => true,
                'status' => $response->status(),
                'body' => $response->json(),
                'error' => null,
            ];
        } catch (ConnectionException $exception) {
            return [
                'ok' => false,
                'status' => null,
                'body' => null,
                'error' => $exception->getMessage(),
            ];
        }
    }

    /**
     * @return array{ok: bool, status: ?int, body: ?array<string, mixed>, error: ?string}
     */
    public function analyzeCvText(string $cvText, string $fileName): array
    {
        try {
            $response = Http::timeout(120)
                ->post($this->baseUrl().'/api/v1/cv/analyze-text', [
                    'cv_text' => $cvText,
                    'file_name' => $fileName,
                ]);

            if (! $response->successful()) {
                $message = $response->json('detail')
                    ?? $response->json('message')
                    ?? $response->body();

                return [
                    'ok' => false,
                    'status' => $response->status(),
                    'body' => null,
                    'error' => is_string($message) ? $message : 'CV analizi başarısız',
                ];
            }

            return [
                'ok' => true,
                'status' => $response->status(),
                'body' => $response->json(),
                'error' => null,
            ];
        } catch (ConnectionException $exception) {
            return [
                'ok' => false,
                'status' => null,
                'body' => null,
                'error' => $exception->getMessage(),
            ];
        }
    }
}
