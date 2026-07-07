<?php

namespace App\Http\Controllers\App;

use App\Data\PanelDemoData;
use App\Services\PanelCvAnalysisStore;

class CareerLadderController extends PanelController
{
    public function show()
    {
        $data = $this->panelApiData('career-ladder', [
            'career_ladder' => PanelDemoData::careerLadder(),
            'career_tier_meta' => PanelDemoData::careerTierMeta(),
        ]);
        $ladder = PanelCvAnalysisStore::careerLadder() ?? $data['career_ladder'];
        $fromApi = PanelCvAnalysisStore::has();

        return $this->panelView('app.career-ladder', [
            'careerLadder' => $ladder,
            'careerTierMeta' => $data['career_tier_meta'],
            'fromApi' => $fromApi,
        ]);
    }
}
