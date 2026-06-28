<?php

namespace App\Http\Controllers;

use App\Services\BalanceSheetService;
use Illuminate\Http\Request;

class ReportBalanceSheetController extends Controller
{
    public function __construct(
        protected BalanceSheetService $balanceSheetService
    ) {}

    public function index(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));
        $data = $this->balanceSheetService->getData($date);
        $availableDates = $this->balanceSheetService->getAvailableDates();

        return view('reports.balance-sheet', array_merge($data, [
            'selectedDate' => $date,
            'availableDates' => $availableDates,
        ]));
    }
}
