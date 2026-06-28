<?php

namespace App\Http\Controllers;

use App\Exports\SalesReportExport;
use App\Services\SalesReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class SalesReportController extends Controller
{
    public function __construct(
        protected SalesReportService $salesReportService
    ) {}

    public function index(Request $request)
    {
        $filters = $this->parseFilters($request);
        $data = $this->salesReportService->getReport($filters);

        return view('sales-report.index', $data);
    }

    public function export(Request $request)
    {
        $filters = $this->parseFilters($request);

        return Excel::download(new SalesReportExport($filters), 'laporan-penjualan.xlsx');
    }

    private function parseFilters(Request $request): array
    {
        $raw = $request->only(['date_from', 'date_to', 'category', 'product', 'account']);
        $raw = array_map(fn($v) => $v === '' ? null : $v, $raw);

        return array_filter(
            Validator::make($raw, [
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date',
                'category' => 'nullable|integer',
                'product' => 'nullable|integer',
                'account' => 'nullable|integer',
            ])->valid(),
            fn($v) => $v !== null
        );
    }
}
