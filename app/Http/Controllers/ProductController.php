<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\StockService;
use App\Exports\ProductsExport;
use App\Imports\ProductsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductController extends Controller
{
    public function __construct(
        protected StockService $stockService
    ) {}

    public function index(Request $request)
    {
        $query = Product::with('category');

        if (!empty($request->search)) {
            $s = addcslashes($request->search, '%_');
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('unit', 'like', "%{$s}%");
            });
        }

        if (!empty($request->category)) {
            $query->where('category_id', $request->category);
        }

        $products = $query->latest()->paginate(50)->withQueryString();
        $categories = ProductCategory::orderBy('name')->get();
        $totalProducts = Product::count();
        $totalStockValue = Product::sum(DB::raw('stock * purchase_price'));
        return view('products.index', compact('products', 'categories', 'totalProducts', 'totalStockValue'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id'    => 'required|exists:product_categories,id',
            'name'           => 'required|string|max:100|unique:products,name',
            'purchase_price' => 'required|integer|min:0',
            'selling_price'  => 'required|integer|min:0',
            'stock'          => 'required|integer|min:0',
            'stock_min'      => 'required|integer|min:0',
            'unit'           => 'required|string|max:20',
        ]);

        $validated['is_active'] = true;

        try {
            Product::create($validated);
            return redirect()->back()->with('success', 'Barang berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan barang: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id'    => 'required|exists:product_categories,id',
            'name'           => 'required|string|max:100|unique:products,name,' . $product->id,
            'purchase_price' => 'required|integer|min:0',
            'selling_price'  => 'required|integer|min:0',
            'stock_min'      => 'required|integer|min:0',
            'unit'           => 'required|string|max:20',
            'is_active'      => 'boolean',
        ]);

        // ponytail: stock hanya bisa berubah melalui StockService
        unset($validated['stock']);

        try {
            $product->update($validated);
            return redirect()->back()->with('success', 'Barang berhasil diubah.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengubah barang: ' . $e->getMessage());
        }
    }

    public function destroy(Product $product)
    {
        try {
            $product->update(['is_active' => false]);
            return redirect()->back()->with('success', 'Barang berhasil dinonaktifkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menonaktifkan barang: ' . $e->getMessage());
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        $deleted = 0;
        foreach ($request->ids as $id) {
            try {
                Product::where('id', $id)->update(['is_active' => false]);
                $deleted++;
            } catch (\Exception $e) {
                // skip
            }
        }
        return redirect()->back()->with('success', "{$deleted} data berhasil dinonaktifkan.");
    }

    public function history(Product $product)
    {
        $result = $this->stockService->getProductHistory($product->id);
        return view('products.history', array_merge(compact('product'), $result));
    }

    public function export(Request $request)
    {
        $filters = [];
        if (!empty($request->category)) {
            $filters['category_id'] = $request->category;
        }
        if (!empty($request->search)) {
            $filters['search'] = $request->search;
        }

        return Excel::download(new ProductsExport($filters), 'daftar-barang.xlsx');
    }

    public function downloadTemplate()
    {
        $headings = new class implements WithHeadings {
            public function headings(): array
            {
                return ['Nama', 'Kategori', 'Harga Beli', 'Harga Jual', 'Stok Minimum', 'Satuan'];
            }
        };

        $data = [
            ['Contoh Barang', 'Pulsa', 5000, 6000, 10, 'pcs'],
        ];

        return Excel::download(new class($data, $headings) implements \Maatwebsite\Excel\Concerns\FromCollection,
            \Maatwebsite\Excel\Concerns\WithHeadings {
            protected array $data;
            protected $headings;

            public function __construct(array $data, $headings)
            {
                $this->data = $data;
                $this->headings = $headings;
            }

            public function collection()
            {
                return collect($this->data);
            }

            public function headings(): array
            {
                return $this->headings->headings();
            }
        }, 'template-barang.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        try {
            $import = new ProductsImport();
            Excel::import($import, $request->file('file'));

            $created = $import->getCreatedCount();
            $updated = $import->getUpdatedCount();

            $msg = "Import selesai. {$created} barang baru, {$updated} barang diperbarui.";
            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }
}
