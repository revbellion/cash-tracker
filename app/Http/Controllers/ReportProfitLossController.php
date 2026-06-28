<?php

namespace App\Http\Controllers;

use App\Services\ProfitLossService;
use Illuminate\Http\Request;

class ReportProfitLossController extends Controller
{
    public function __construct(
        protected ProfitLossService $profitLossService
    ) {}

    public function index(Request $request)
    {
        $period = $request->input('period', date('Y-m'));
        $data = $this->profitLossService->getData($period);
        $availablePeriods = $this->profitLossService->getAvailablePeriods();

        return view('reports.profit-loss', array_merge($data, [
            'selectedPeriod' => $period,
            'availablePeriods' => $availablePeriods,
        ]));
    }
}
