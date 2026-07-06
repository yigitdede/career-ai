<?php

namespace App\Http\Controllers\Admin;

use App\Data\AdminDemoData;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    public function dashboard()
    {
        return $this->adminView('admin.dashboard', AdminDemoData::dashboard());
    }

    public function students() { return $this->page('students'); }
    public function cohorts() { return $this->page('cohorts'); }
    public function readiness() { return $this->page('readiness'); }
    public function skillPassport() { return $this->page('skill-passport'); }
    public function jobRadar() { return $this->page('job-radar'); }
    public function applications() { return $this->page('applications'); }
    public function interviews() { return $this->page('interviews'); }
    public function mentors() { return $this->page('mentors'); }
    public function learning() { return $this->page('learning'); }
    public function settings() { return $this->page('settings'); }

    private function page(string $key)
    {
        $pages = AdminDemoData::pages();
        abort_unless(isset($pages[$key]), 404);

        return $this->adminView('admin.page', [
            'page' => $pages[$key],
            'moduleKey' => $key,
        ]);
    }

    private function adminView(string $view, array $data = [])
    {
        return view($view, array_merge($data, [
            'adminNav' => AdminDemoData::nav(),
        ]));
    }
}
