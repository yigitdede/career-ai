<?php

namespace App\Http\Controllers\App;

use App\Data\PanelDemoData;
use App\Services\PanelCvAnalysisStore;

class DashboardController extends PanelController
{
    public function index()
    {
        $hasCvAnalysis = PanelCvAnalysisStore::has();
        $skillRadar = PanelCvAnalysisStore::skillRadar();
        $cvFileName = PanelCvAnalysisStore::fileName();
        $ladder = PanelCvAnalysisStore::careerLadder();
        $data = $this->panelApiData('dashboard', [
            'stats' => PanelDemoData::stats(),
            'weekly_tasks' => PanelDemoData::weeklyTasks(),
            'learning_resources' => PanelDemoData::learningResources(),
        ]);

        $stats = $data['stats'];
        if (is_array($ladder) && isset($ladder[0])) {
            $stats['career'] = (string) ($ladder[0]['title'] ?? $stats['career']);
            $stats['readiness'] = (int) ($ladder[0]['readiness'] ?? $stats['readiness']);
        }

        return $this->panelView('app.dashboard', [
            'stats' => $stats,
            'weeklyTasks' => $data['weekly_tasks'],
            'learningResources' => $data['learning_resources'],
            'skillRadar' => $skillRadar,
            'hasCvAnalysis' => $hasCvAnalysis,
            'cvFileName' => $cvFileName,
        ]);
    }
}
