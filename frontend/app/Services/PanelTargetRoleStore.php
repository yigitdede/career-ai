<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class PanelTargetRoleStore
{
    public const SESSION_KEY = 'panel_target_role';

    /**
     * @param  array<string, mixed>  $role
     * @return array<string, mixed>
     */
    public static function putLadderRole(array $role): array
    {
        return self::persist([
            'source' => 'ladder',
            'role_id' => (string) ($role['id'] ?? Str::slug((string) ($role['title'] ?? 'hedef-rol'))),
            'title' => (string) ($role['title'] ?? 'Hedef rol'),
            'readiness' => (int) ($role['readiness'] ?? 0),
            'gap_count' => (int) ($role['gap_count'] ?? 0),
            'gaps_summary' => (string) ($role['gaps_summary'] ?? ''),
            'weeks_estimate' => $role['weeks_estimate'] ?? null,
            'swot' => $role['swot'] ?? null,
            'required_skills' => self::skillsFromRole($role),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function putCustomRole(string $title): array
    {
        $cleanTitle = trim($title);

        return self::persist([
            'source' => 'custom',
            'role_id' => 'custom-'.Str::slug($cleanTitle),
            'title' => $cleanTitle,
            'readiness' => 35,
            'gap_count' => 4,
            'gaps_summary' => 'Rol gereksinimleri, portfolio, CV uyumu, başvuru planı',
            'weeks_estimate' => '4–8 hafta',
            'required_skills' => ['Rol gereksinimleri', 'Portfolio kanıtı', 'CV anahtar kelimeleri'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function putJobUrl(string $url): array
    {
        $cleanUrl = trim($url);
        $parsed = self::parseJobListing($cleanUrl);
        $title = (string) ($parsed['title'] ?? self::titleFromUrl($cleanUrl));
        $host = parse_url($cleanUrl, PHP_URL_HOST) ?: 'ilan';

        return self::persist([
            'source' => 'job_url',
            'role_id' => (string) ($parsed['role_id'] ?? 'job-'.Str::slug($host.'-'.$title)),
            'title' => 'İlan hedefi: '.$title,
            'job_url' => (string) ($parsed['url'] ?? $cleanUrl),
            'readiness' => 30,
            'gap_count' => max(3, count($parsed['required_skills'] ?? [])),
            'gaps_summary' => implode(', ', $parsed['required_skills'] ?? ['İlan gereksinimleri', 'anahtar kelimeler', 'CV uyumu']),
            'weeks_estimate' => '2–4 hafta',
            'parsed_from' => $parsed['parsed_from'] ?? 'url',
            'required_skills' => $parsed['required_skills'] ?? ['İlan gereksinimleri', 'CV anahtar kelimeleri'],
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function get(): ?array
    {
        $result = app(CareerTalentApiClient::class)->panelTarget();
        $target = $result['body']['target'] ?? null;
        if (($result['ok'] ?? false) && is_array($target)) {
            Session::put(self::SESSION_KEY, $target);

            return $target;
        }

        $sessionTarget = Session::get(self::SESSION_KEY);

        return is_array($sessionTarget) ? $sessionTarget : null;
    }

    public static function storageKey(): string
    {
        $target = self::get();
        if (! $target) {
            return 'panel-weekly-tasks-default';
        }

        return 'panel-weekly-tasks-'.Str::slug((string) ($target['role_id'] ?? $target['title'] ?? 'target'));
    }

    /**
     * @param  array<string, mixed>  $target
     * @return array<string, mixed>
     */
    private static function persist(array $target): array
    {
        $target['selected_at'] = now()->toIso8601String();
        Session::put(self::SESSION_KEY, $target);

        $result = app(CareerTalentApiClient::class)->savePanelTarget($target);
        $apiTarget = $result['body']['target'] ?? null;
        if (($result['ok'] ?? false) && is_array($apiTarget)) {
            Session::put(self::SESSION_KEY, $apiTarget);

            return $apiTarget;
        }

        return $target;
    }

    /**
     * @return array<string, mixed>
     */
    private static function parseJobListing(string $url): array
    {
        $result = app(CareerTalentApiClient::class)->parseJobListing($url);
        $body = $result['body'] ?? null;

        return (($result['ok'] ?? false) && is_array($body)) ? $body : [];
    }

    private static function titleFromUrl(string $url): string
    {
        $path = trim((string) (parse_url($url, PHP_URL_PATH) ?: ''), '/');
        $slug = $path !== '' ? basename($path) : (parse_url($url, PHP_URL_HOST) ?: 'İş ilanı');

        return Str::of($slug)->replace(['-', '_'], ' ')->title()->toString();
    }

    /**
     * @param  array<string, mixed>  $role
     * @return list<string>
     */
    private static function skillsFromRole(array $role): array
    {
        $swot = $role['swot'] ?? null;
        if (is_array($swot) && isset($swot['weaknesses']) && is_array($swot['weaknesses'])) {
            return array_values(array_filter($swot['weaknesses'], 'is_string'));
        }

        $summary = (string) ($role['gaps_summary'] ?? '');

        return array_values(array_filter(array_map('trim', explode(',', $summary))));
    }
}
