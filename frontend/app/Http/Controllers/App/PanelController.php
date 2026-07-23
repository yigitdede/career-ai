<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Services\CareerTalentApiClient;

abstract class PanelController extends Controller
{
    protected function panelView(string $view, array $data = [])
    {
        $api = app(CareerTalentApiClient::class);

        return view($view, array_merge($data, [
            'apiHealth' => $api->health(),
            'apiUrl' => $api->baseUrl(),
            'panelUser' => [
                'name' => session('auth.user.full_name', __('panel.account.user_fallback')),
                'avatar_url' => null,
            ],
        ]));
    }

}
