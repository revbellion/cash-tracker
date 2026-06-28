<?php

namespace App\Http\Controllers;

use App\Models\HppRecord;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HppRecordController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->get('date_from', date('Y-m-d'));
        $dateTo = $request->get('date_to', date('Y-m-d'));
        $categoryFilter = $request->get('category');
        $search = $request->get('search');
        $tab = $request->get('tab', 'divisions');

        $query = HppRecord::with('product', 'category')
            ->whereBetween('date', [$dateFrom, $dateTo]);

        if ($categoryFilter) {
            $query->where('product_category_id', $categoryFilter);
        }

        if ($search) {
            $s = addcslashes($search, '%_');
            $query->whereHas('product', fn($q) => $q->where('name', 'like', "%{$s}%"));
        }

        $records = $query->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $summary = [
            'total_hpp'     => $records->sum('hpp_amount'),
            'total_selling' => $records->sum('selling_amount'),
            'total_profit'  => $records->sum('profit_amount'),
        ];

        $receipts = $records->groupBy('receipt_id')->map(function ($items) {
            return [
                'receipt_id'     => $items->first()->receipt_id,
                'date'           => $items->first()->date,
                'items'          => $items,
                'total_hpp'      => $items->sum('hpp_amount'),
                'total_selling'  => $items->sum('selling_amount'),
                'total_profit'   => $items->sum('profit_amount'),
            ];
        })->values();

        $categories = ProductCategory::orderBy('name')->get();

        // === Per-Division Summary ===
        $divisionSummary = $this->getDivisionSummary($dateFrom, $dateTo, $categoryFilter);

        return view('hpp-records.index', compact(
            'receipts', 'summary', 'dateFrom', 'dateTo',
            'categories', 'categoryFilter', 'search', 'tab',
            'divisionSummary'
        ));
    }

    private function getDivisionSummary(string $dateFrom, string $dateTo, ?string $categoryFilter): array
    {
        // 1. Penjualan & HPP per kategori (dari HppRecord)
        $salesQuery = HppRecord::whereBetween('date', [$dateFrom, $dateTo])
            ->select(
                'product_category_id',
                DB::raw('SUM(selling_amount) as total_selling'),
                DB::raw('SUM(hpp_amount) as total_hpp'),
                DB::raw('SUM(profit_amount) as total_profit'),
                DB::raw('SUM(qty) as total_qty')
            )
            ->groupBy('product_category_id');

        if ($categoryFilter) {
            $salesQuery->where('product_category_id', $categoryFilter);
        }

        $salesData = $salesQuery->get()->keyBy('product_category_id');

        // 2. Pembelian stok per kategori (dari StockTransaction -> Product -> category_id)
        $purchaseQuery = StockTransaction::where('type', 'in')
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->join('products', 'stock_transactions.product_id', '=', 'products.id')
            ->select(
                'products.category_id',
                DB::raw('SUM(stock_transactions.qty * stock_transactions.price) as total_purchase'),
                DB::raw('SUM(stock_transactions.qty) as total_qty_in')
            )
            ->groupBy('products.category_id');

        if ($categoryFilter) {
            $purchaseQuery->where('products.category_id', $categoryFilter);
        }

        $purchasesByCategory = $purchaseQuery->pluck('total_purchase', 'category_id');

        // 3. Nilai stok per kategori (dari Product active)
        $stockQuery = Product::active()
            ->select(
                'category_id',
                DB::raw('SUM(stock * purchase_price) as total_stock_value')
            )
            ->groupBy('category_id');

        if ($categoryFilter) {
            $stockQuery->where('category_id', $categoryFilter);
        }

        $stockByCategory = $stockQuery->pluck('total_stock_value', 'category_id');

        // 4. Gabung semua data per kategori
        $categories = ProductCategory::orderBy('name')->get();
        $divisions = [];
        $grandTotal = [
            'selling' => 0, 'hpp' => 0, 'profit' => 0,
            'purchase' => 0, 'stock_value' => 0, 'qty' => 0,
        ];

        foreach ($categories as $cat) {
            $row = $salesData->get($cat->id);
            $selling = (int) ($row->total_selling ?? 0);
            $hpp = (int) ($row->total_hpp ?? 0);
            $profit = (int) ($row->total_profit ?? 0);
            $purchase = (int) ($purchasesByCategory[$cat->id] ?? 0);
            $stockValue = (int) ($stockByCategory[$cat->id] ?? 0);
            $qty = (int) ($row->total_qty ?? 0);

            if ($selling == 0 && $hpp == 0 && $purchase == 0 && $stockValue == 0) {
                continue;
            }

            $divisions[] = [
                'id' => $cat->id,
                'name' => $cat->name,
                'selling' => $selling,
                'hpp' => $hpp,
                'profit' => $profit,
                'margin' => $selling > 0 ? round(($profit / $selling) * 100, 1) : 0,
                'purchase' => $purchase,
                'stock_value' => $stockValue,
                'qty' => $qty,
            ];

            $grandTotal['selling'] += $selling;
            $grandTotal['hpp'] += $hpp;
            $grandTotal['profit'] += $profit;
            $grandTotal['purchase'] += $purchase;
            $grandTotal['stock_value'] += $stockValue;
            $grandTotal['qty'] += $qty;
        }

        $grandTotal['margin'] = $grandTotal['selling'] > 0
            ? round(($grandTotal['profit'] / $grandTotal['selling']) * 100, 1)
            : 0;

        return [
            'divisions' => $divisions,
            'grandTotal' => $grandTotal,
        ];
    }
}
