<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Session;

class PanelCvAnalysisStore
{
    public const SESSION_KEY = 'cv_analysis';

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function put(array $payload, string $fileName, string $source = 'upload'): void
    {
        Session::put(self::SESSION_KEY, [
            'file_name' => $fileName,
            'source' => $source,
            'profile' => $payload['profile'] ?? null,
            'skill_radar' => $payload['skill_radar'] ?? null,
            'career_ladder' => $payload['career_ladder'] ?? [],
            'analyzed_at' => now()->toIso8601String(),
        ]);
    }

    public static function source(): ?string
    {
        return Session::get(self::SESSION_KEY.'.source');
    }

    public static function has(): bool
    {
        return self::careerLadder() !== null || self::skillRadar() !== null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function careerLadder(): ?array
    {
        $ladder = Session::get(self::SESSION_KEY.'.career_ladder');

        return is_array($ladder) && $ladder !== [] ? $ladder : null;
    }

  /**
     * @return array<string, mixed>|null
     */
    public static function skillRadar(): ?array
    {
        $radar = Session::get(self::SESSION_KEY.'.skill_radar');

        return is_array($radar) ? $radar : null;
    }

    public static function fileName(): ?string
    {
        return Session::get(self::SESSION_KEY.'.file_name');
    }

    public static function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }
}
