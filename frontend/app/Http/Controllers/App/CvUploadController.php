<?php

namespace App\Http\Controllers\App;

use App\Services\BuilderCvTextExporter;
use App\Services\CareerTalentApiClient;
use App\Services\PanelCvAnalysisStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CvUploadController extends PanelController
{
    public function analyze(Request $request, CareerTalentApiClient $api): JsonResponse
    {
        $request->validate([
            'cv' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $file = $request->file('cv');
        $result = $api->analyzeCv($file);

        if (! ($result['ok'] ?? false)) {
            return response()->json([
                'message' => $result['error'] ?? __('panel.profile.cv_analyze_failed'),
            ], $result['status'] ?? 502);
        }

        $body = $result['body'] ?? [];
        PanelCvAnalysisStore::put($body, $file->getClientOriginalName(), 'upload');

        return response()->json([
            'status' => 'ready',
            'file_name' => $file->getClientOriginalName(),
            'skill_radar' => $body['skill_radar'] ?? null,
            'career_ladder' => $body['career_ladder'] ?? [],
            'redirect' => route('panel.career-ladder'),
        ]);
    }

    public function analyzeBuilder(Request $request, CareerTalentApiClient $api): JsonResponse
    {
        $validated = $request->validate([
            'locales' => ['required', 'array'],
            'locale' => ['nullable', 'string', 'in:tr,en'],
        ]);

        $locale = $validated['locale'] ?? app()->getLocale();
        $locales = $validated['locales'];
        $cvText = BuilderCvTextExporter::toText($locales, $locale);

        if (strlen($cvText) < 40) {
            return response()->json([
                'message' => __('panel.cv_builder.analyze_too_short'),
            ], 422);
        }

        $fileName = BuilderCvTextExporter::fileName($locales, $locale);
        $result = $api->analyzeCvText($cvText, $fileName);

        if (! ($result['ok'] ?? false)) {
            return response()->json([
                'message' => $result['error'] ?? __('panel.profile.cv_analyze_failed'),
            ], $result['status'] ?? 502);
        }

        $body = $result['body'] ?? [];
        PanelCvAnalysisStore::put($body, $fileName, 'builder');

        return response()->json([
            'status' => 'ready',
            'file_name' => $fileName,
            'skill_radar' => $body['skill_radar'] ?? null,
            'career_ladder' => $body['career_ladder'] ?? [],
            'redirect' => route('panel.cv-builder'),
        ]);
    }

    public function clear(): JsonResponse
    {
        PanelCvAnalysisStore::clear();

        return response()->json(['status' => 'cleared']);
    }
}
