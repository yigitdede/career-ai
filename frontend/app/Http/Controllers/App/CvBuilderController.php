<?php

namespace App\Http\Controllers\App;

use App\Data\PanelDemoData;
use App\Services\PanelCvAnalysisStore;
use Illuminate\Support\Facades\Lang;

class CvBuilderController extends PanelController
{
    public function show()
    {
        $hasCvAnalysis = PanelCvAnalysisStore::has();

        return $this->panelView('app.cv-builder', [
            'cvDraft' => PanelDemoData::cvDraft(),
            'cvLabels' => $this->cvLabelsForJs(),
            'skillRadar' => PanelCvAnalysisStore::skillRadar(),
            'hasCvAnalysis' => $hasCvAnalysis,
            'cvFileName' => PanelCvAnalysisStore::fileName(),
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function cvLabelsForJs(): array
    {
        $labels = [];

        foreach (['tr', 'en'] as $locale) {
            $labels[$locale] = Lang::get('panel.cv_builder', [], $locale);
        }

        return $labels;
    }
}
