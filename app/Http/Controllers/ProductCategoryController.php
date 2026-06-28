<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::withCount('products')->latest()->get();
        $totalCategories = $categories->count();
        return view('product-categories.index', compact('categories', 'totalCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:product_categories,name',
        ]);

        try {
            ProductCategory::create($validated);
            return redirect()->back()->with('success', 'Kategori berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan kategori: ' . $e->getMessage());
        }
    }

    public function update(Request $request, ProductCategory $productCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:product_categories,name,' . $productCategory->id,
        ]);

        try {
            $productCategory->update($validated);
            return redirect()->back()->with('success', 'Kategori berhasil diubah.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengubah kategori: ' . $e->getMessage());
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        $deleted = 0;
        foreach ($request->ids as $id) {
            try {
                $productCategory = ProductCategory::findOrFail($id);
                if ($productCategory->products()->exists()) {
                    $defaultCategory = ProductCategory::firstOrCreate(['name' => 'Lainnya']);
                    $productCategory->products()->update(['category_id' => $defaultCategory->id]);
                }
                $productCategory->delete();
                $deleted++;
            } catch (\Exception $e) {
                // skip
            }
        }
        return redirect()->back()->with('success', "{$deleted} data berhasil dihapus.");
    }

    public function destroy(ProductCategory $productCategory)
    {
        try {
            // Pindahkan produk ke kategori "Lainnya" jika ada
            if ($productCategory->products()->exists()) {
                $defaultCategory = ProductCategory::firstOrCreate(['name' => 'Lainnya']);
                $productCategory->products()->update(['category_id' => $defaultCategory->id]);
            }

            $productCategory->delete();
            return redirect()->back()->with('success', 'Kategori berhasil dihapus. Produk dipindahkan ke "Lainnya".');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus kategori: ' . $e->getMessage());
        }
    }
}
