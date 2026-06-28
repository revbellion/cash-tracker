<?php

namespace App\Services;

use App\Models\Account;
use App\Models\HppRecord;
use App\Models\Income;
use App\Models\Product;
use App\Models\ProductCategory;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SalesReportService
{
    public function getReport(array $filters): array
    {
        $dateFrom = $filters['date_from'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $filters['date_to'] ?? Carbon::now()->format('Y-m-d');
        $categoryFilter = $filters['category'] ?? null;
        $productFilter = $filters['product'] ?? null;
        $accountFilter = $filters['account'] ?? null;

        $query = HppRecord::with(['product', 'category', 'income.account'])
            ->whereBetween('date', [$dateFrom, $dateTo]);

        if ($categoryFilter) {
            $query->where('product_category_id', $categoryFilter);
        }

        if ($productFilter) {
            $query->where('product_id', $productFilter);
        }

        if ($accountFilter) {
            $query->whereHas('income', fn($q) => $q->where('account_id', $accountFilter));
        }

        $records = $query->orderBy('date', 'desc')->orderBy('id', 'desc')->get();

        // Summary cards
        $summary = [
            'total_revenue' => $records->sum('selling_amount'),
            'total_hpp' => $records->sum('hpp_amount'),
            'total_profit' => $records->sum('profit_amount'),
            'total_transactions' => $records->pluck('receipt_id')->filter()->unique()->count(),
            'total_qty' => $records->sum('qty'),
        ];
        $summary['avg_transaction'] = $summary['total_transactions'] > 0
            ? (int) round($summary['total_revenue'] / $summary['total_transactions'])
            : 0;

        // Daily sales trend
        $dailySales = $records->groupBy(fn($r) => $r->date->format('Y-m-d'))
            ->map(fn($items, $date) => [
                'date' => $date,
                'label' => Carbon::parse($date)->locale('id')->isoFormat('D MMM'),
                'revenue' => $items->sum('selling_amount'),
                'profit' => $items->sum('profit_amount'),
                'transactions' => $items->pluck('receipt_id')->filter()->unique()->count(),
            ])
            ->values()
            ->sortBy('date')
            ->values();

        // Top products
        $topProducts = $records->groupBy('product_id')
            ->map(function ($items) {
                $product = $items->first()->product;
                return [
                    'product_id' => $items->first()->product_id,
                    'name' => $product?->name ?? 'Unknown',
                    'category' => $items->first()->category?->name ?? '-',
                    'qty' => $items->sum('qty'),
                    'revenue' => $items->sum('selling_amount'),
                    'profit' => $items->sum('profit_amount'),
                ];
            })
            ->sortByDesc('revenue')
            ->values()
            ->take(10);

        // Sales by category
        $salesByCategory = $records->groupBy('product_category_id')
            ->map(function ($items) {
                return [
                    'category_id' => $items->first()->product_category_id,
                    'name' => $items->first()->category?->name ?? 'Tanpa Kategori',
                    'qty' => $items->sum('qty'),
                    'revenue' => $items->sum('selling_amount'),
                    'profit' => $items->sum('profit_amount'),
                ];
            })
            ->sortByDesc('revenue')
            ->values();

        // Sales by account (payment method)
        $salesByAccount = $records->filter(fn($r) => $r->income?->account)
            ->groupBy(fn($r) => $r->income->account_id)
            ->map(function ($items) {
                $account = $items->first()->income->account;
                return [
                    'account_id' => $account->id,
                    'name' => $account->name,
                    'type' => $account->type,
                    'revenue' => $items->sum('selling_amount'),
                    'transactions' => $items->pluck('receipt_id')->filter()->unique()->count(),
                ];
            })
            ->sortByDesc('revenue')
            ->values();

        // Detail per receipt for table
        $receipts = $records->groupBy('receipt_id')
            ->map(function ($items) {
                return [
                    'receipt_id' => $items->first()->receipt_id,
                    'date' => $items->first()->date,
                    'account' => $items->first()->income?->account?->name ?? '-',
                    'items' => $items,
                    'total_qty' => $items->sum('qty'),
                    'total_revenue' => $items->sum('selling_amount'),
                    'total_hpp' => $items->sum('hpp_amount'),
                    'total_profit' => $items->sum('profit_amount'),
                ];
            })
            ->sortByDesc('date')
            ->values();

        // Filter options
        $categories = ProductCategory::orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $accounts = Account::where('is_active', true)->orderBy('name')->get();

        return compact(
            'summary', 'dailySales', 'topProducts', 'salesByCategory',
            'salesByAccount', 'receipts', 'categories', 'products', 'accounts',
            'dateFrom', 'dateTo', 'categoryFilter', 'productFilter', 'accountFilter'
        );
    }

    public function getExportData(array $filters): Collection
    {
        $dateFrom = $filters['date_from'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $filters['date_to'] ?? Carbon::now()->format('Y-m-d');

        $query = HppRecord::with(['product', 'category', 'income.account'])
            ->whereBetween('date', [$dateFrom, $dateTo]);

        if (!empty($filters['category'])) {
            $query->where('product_category_id', $filters['category']);
        }
        if (!empty($filters['product'])) {
            $query->where('product_id', $filters['product']);
        }
        if (!empty($filters['account'])) {
            $query->whereHas('income', fn($q) => $q->where('account_id', $filters['account']));
        }

        return $query->orderBy('date', 'desc')->orderBy('id', 'desc')->get();
    }
}
