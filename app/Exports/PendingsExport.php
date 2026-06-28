<?php

namespace App\Exports;

use App\Models\PendingTransaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PendingsExport implements FromCollection, WithHeadings, WithMapping
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = PendingTransaction::query();

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }
        if (!empty($this->filters['type'])) {
            $query->where('type', $this->filters['type']);
        }
        if (!empty($this->filters['search'])) {
            $s = addcslashes($this->filters['search'], '%_');
            $query->where('description', 'like', "%{$s}%");
        }

        return $query->latest('pending_date')->get();
    }

    public function headings(): array
    {
        return ['Tanggal', 'Tipe', 'Deskripsi', 'Nominal', 'MDR', 'Net', 'Status'];
    }

    public function map($row): array
    {
        return [
            $row->pending_date->format('d/m/Y H:i'),
            $row->type_label,
            $row->description,
            $row->amount,
            $row->mdr_amount,
            $row->net_amount,
            $row->status === 'completed' ? 'Selesai' : 'Pending',
        ];
    }
}
