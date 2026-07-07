<?php

namespace App\Http\Controllers\App;

use App\Data\PanelDemoData;

class RoadmapController extends PanelController
{
    public function show()
    {
        $data = $this->panelApiData('roadmap', [
            'stats' => PanelDemoData::stats(),
            'weekly_tasks' => PanelDemoData::weeklyTasks(),
        ]);

        return $this->panelView('app.roadmap', [
            'stats' => $data['stats'],
            'roadmapTasks' => $data['weekly_tasks'],
        ]);
    }
}
