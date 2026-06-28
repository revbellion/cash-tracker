<?php

namespace App\Exports;

use App\Services\SalesReportService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class SalesReportExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected array $filters;
    protected SalesReportService $service;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
        $this->service = new SalesReportService();
    }

    public function collection()
    {
        return $this->service->getExportData($this->filters);
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'No. Struk',
            'Produk',
            'Kategori',
            'Qty',
            'HPP/pc',
            'Jual/pc',
            'Total HPP',
            'Total Jual',
            'Profit',
            'Metode Bayar',
        ];
    }

    public function map($row): array
    {
        return [
            $row->date->format('d/m/Y'),
            $row->receipt_id ?? 'Manual',
            $row->product?->name ?? '-',
            $row->category?->name ?? '-',
            $row->qty,
            (int) round($row->hpp_amount / max($row->qty, 1)),
            (int) round($row->selling_amount / max($row->qty, 1)),
            $row->hpp_amount,
            $row->selling_amount,
            $row->profit_amount,
            $row->income?->account?->name ?? '-',
        ];
    }

    public function title(): string
    {
        return 'Laporan Penjualan';
    }
}
