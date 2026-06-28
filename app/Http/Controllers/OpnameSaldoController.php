<?php

namespace App\Http\Controllers;

use App\Services\OpnameSaldoService;
use Illuminate\Http\Request;

class OpnameSaldoController extends Controller
{
    public function __construct(
        protected OpnameSaldoService $opnameService
    ) {}

    public function index(Request $request)
    {
        $date = $request->get('date', date('Y-m-d'));
        $balances = $this->opnameService->getAccountBalances($date);
        $history = $this->opnameService->getOpnameHistory($date);

        return view('opname-saldo.index', compact('balances', 'date', 'history'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'accounts' => 'required|array',
        ]);

        try {
            $result = $this->opnameService->processOpname($validated, $validated['date']);

            $message = 'Opname saldo berhasil disimpan.';
            if (!empty($result['warnings'])) {
                $message .= ' ' . implode(' ', $result['warnings']);
                return redirect()->back()->with('warning', $message);
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan opname: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $this->opnameService->delete($id);
            return redirect()->back()->with('success', 'Riwayat opname berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus riwayat opname: ' . $e->getMessage());
        }
    }
}
