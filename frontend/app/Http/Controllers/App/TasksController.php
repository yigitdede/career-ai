<?php

namespace App\Http\Controllers\App;

use App\Data\PanelDemoData;

class TasksController extends PanelController
{
    public function show()
    {
        $data = $this->panelApiData('tasks', [
            'stats' => PanelDemoData::stats(),
            'weekly_tasks' => PanelDemoData::weeklyTasks(),
        ]);

        return $this->panelView('app.tasks', [
            'weeklyTasks' => $data['weekly_tasks'],
            'stats' => $data['stats'],
        ]);
    }
}
