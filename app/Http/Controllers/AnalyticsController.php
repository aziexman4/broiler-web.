<?php

namespace App\Http\Controllers;

use App\Services\FlockMetricsService;

class AnalyticsController extends Controller
{
    public function index()
    {
        return view('analytics.index', FlockMetricsService::buildBenchmarkReport());
    }
}
