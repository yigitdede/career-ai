<?php

namespace App\Http\Controllers\App;

use App\Data\PanelDemoData;

class ChatController extends PanelController
{
    public function show()
    {
        $data = $this->panelApiData('chat', [
            'assistant' => PanelDemoData::chatAssistant(),
        ]);

        return $this->panelView('app.chat', [
            'assistant' => $data['assistant'],
        ]);
    }
}
