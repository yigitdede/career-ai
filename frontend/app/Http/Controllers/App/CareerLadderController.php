<?php

namespace App\Http\Controllers\App;

use App\Data\PanelDemoData;
use App\Services\PanelCvAnalysisStore;

class CareerLadderController extends PanelController
{
    public function show()
    {
        $ladder = PanelCvAnalysisStore::careerLadder() ?? PanelDemoData::careerLadder();
        $fromApi = PanelCvAnalysisStore::has();

        return $this->panelView('app.career-ladder', [
            'careerLadder' => $ladder,
            'careerTierMeta' => PanelDemoData::careerTierMeta(),
            'fromApi' => $fromApi,
        ]);
    }
}
