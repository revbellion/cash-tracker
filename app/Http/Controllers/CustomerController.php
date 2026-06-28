<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Services\CustomerService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(
        protected CustomerService $customerService
    ) {}

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'status' => $request->input('status'),
        ];

        $data = $this->customerService->getAll($filters);

        return view('customers.index', array_merge($data, [
            'filters' => $filters,
        ]));
    }

    public function store(StoreCustomerRequest $request)
    {
        try {
            $this->customerService->create($request->validated());
            return redirect()->back()->with('success', 'Pelanggan berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan pelanggan: ' . $e->getMessage());
        }
    }

    public function update(UpdateCustomerRequest $request, $id)
    {
        try {
            $this->customerService->update($id, $request->validated());
            return redirect()->back()->with('success', 'Data pelanggan berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui pelanggan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $this->customerService->delete($id);
            return redirect()->back()->with('success', 'Pelanggan berhasil dinonaktifkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus pelanggan: ' . $e->getMessage());
        }
    }

    public function history($id)
    {
        $data = $this->customerService->getHistory($id);
        return view('customers.history', $data);
    }

    public function search(Request $request)
    {
        $query = $request->input('q', '');
        $results = $this->customerService->search($query);
        return response()->json($results);
    }
}
