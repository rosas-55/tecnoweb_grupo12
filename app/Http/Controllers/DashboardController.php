<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        $widgets = $this->dashboardService->getWidgets();
        $graficas = $this->dashboardService->getGraficas();
        $pageVisits = $this->dashboardService->getPageVisits();
        
        return Inertia::render('Dashboard', [
            'widgets' => $widgets,
            'graficas' => $graficas,
            'pageVisits' => $pageVisits
        ]);
    }
}
