<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromCollection, WithHeadings, WithMapping
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Product::with('category');

        if (!empty($this->filters['category_id'])) {
            $query->where('category_id', $this->filters['category_id']);
        }
        if (!empty($this->filters['search'])) {
            $s = addcslashes($this->filters['search'], '%_');
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('unit', 'like', "%{$s}%");
            });
        }

        return $query->orderBy('name')->get();
    }

    public function headings(): array
    {
        return ['Nama', 'Kategori', 'Harga Beli', 'Harga Jual', 'Stok', 'Stok Minimal', 'Satuan'];
    }

    public function map($row): array
    {
        return [
            $row->name,
            $row->category?->name ?? '-',
            $row->purchase_price,
            $row->selling_price,
            $row->stock,
            $row->stock_min,
            $row->unit,
        ];
    }
}
