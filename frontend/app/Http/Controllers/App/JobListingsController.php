<?php

namespace App\Http\Controllers\App;

use App\Services\CareerTalentApiClient;

class JobListingsController extends PanelController
{
    public function index(CareerTalentApiClient $api)
    {
        $result = $api->publicPositions(['limit' => 100, 'offset' => 0]);
        $realItems = $result['ok'] && is_array(data_get($result, 'body.items'))
            ? collect(data_get($result, 'body.items'))
                ->filter(fn ($item) => is_array($item) && is_array(data_get($item, 'position')))
                ->values()
                ->all()
            : [];
        $documents = $api->cvDocuments();
        $versions  = $api->cvVersions();

        return $this->panelView('app.job-listings', [
            'jobListings' => $realItems,
            'listingsError' => $result['ok'] ? null : ($result['error'] ?? __('panel.job_listings.load_error')),
            'cvDocuments' => $documents['ok'] && is_array($documents['body']) ? $documents['body'] : [],
            'cvVersions' => $versions['ok'] && is_array($versions['body']) ? $versions['body'] : [],
        ]);
    }
}
