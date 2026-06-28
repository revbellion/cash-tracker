<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductCategory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Str;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation
{
    protected int $created = 0;
    protected int $updated = 0;

    public function model(array $row)
    {
        $categoryName = trim($row['kategori'] ?? '');
        $category = ProductCategory::whereRaw('LOWER(name) = ?', [Str::lower($categoryName)])->first();

        if (!$category) {
            $category = ProductCategory::create(['name' => $categoryName]);
        }

        $name = trim($row['nama'] ?? '');

        $existing = Product::whereRaw('LOWER(name) = ?', [Str::lower($name)])->first();

        if ($existing) {
            $existing->update([
                'category_id'    => $category->id,
                'purchase_price' => (int) ($row['harga_beli'] ?? 0),
                'selling_price'  => (int) ($row['harga_jual'] ?? 0),
                'stock_min'      => (int) ($row['stok_minimum'] ?? 0),
                'unit'           => trim($row['satuan'] ?? 'pcs'),
            ]);
            $this->updated++;
            return null;
        }

        $this->created++;

        return new Product([
            'category_id'    => $category->id,
            'name'           => $name,
            'purchase_price' => (int) ($row['harga_beli'] ?? 0),
            'selling_price'  => (int) ($row['harga_jual'] ?? 0),
            'stock'          => 0,
            'stock_min'      => (int) ($row['stok_minimum'] ?? 0),
            'unit'           => trim($row['satuan'] ?? 'pcs'),
            'is_active'      => true,
        ]);
    }

    public function rules(): array
    {
        return [
            'nama'          => 'required|string|max:100',
            'kategori'      => 'required|string|max:100',
            'harga_beli'    => 'required|integer|min:0',
            'harga_jual'    => 'required|integer|min:0',
            'stok_minimum'  => 'nullable|integer|min:0',
            'satuan'        => 'nullable|string|max:20',
        ];
    }

    public function getCreatedCount(): int
    {
        return $this->created;
    }

    public function getUpdatedCount(): int
    {
        return $this->updated;
    }
}
