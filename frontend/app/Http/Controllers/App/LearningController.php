<?php

namespace App\Http\Controllers\App;

use App\Data\PanelDemoData;

class LearningController extends PanelController
{
    public function show()
    {
        $data = $this->panelApiData('learning', [
            'learning_resources' => PanelDemoData::learningResources(),
        ]);

        return $this->panelView('app.learning', [
            'learningResources' => $data['learning_resources'],
        ]);
    }
}
