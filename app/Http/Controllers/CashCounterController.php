<?php

namespace App\Http\Controllers;

use App\Models\CashCounterSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashCounterController extends Controller
{
    public function index(): View
    {
        return view('cash-counter.index');
    }

    public function history(): JsonResponse
    {
        $sessions = CashCounterSession::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get(['id', 'title', 'total_amount', 'created_at']);

        return response()->json($sessions);
    }

    public function show(CashCounterSession $session): JsonResponse
    {
        if ($session->user_id !== auth()->id()) {
            abort(403);
        }
        return response()->json($session);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'denominations' => 'required|array',
            'target_amount' => 'nullable|integer|min:0',
            'total_amount' => 'required|integer|min:0',
        ]);

        $session = CashCounterSession::create([
            'user_id' => auth()->id(),
            'title' => $data['title'],
            'denominations' => $data['denominations'],
            'target_amount' => $data['target_amount'],
            'total_amount' => $data['total_amount'],
        ]);

        return response()->json($session);
    }

    public function update(Request $request, CashCounterSession $session): JsonResponse
    {
        if ($session->user_id !== auth()->id()) {
            abort(403);
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'denominations' => 'required|array',
            'target_amount' => 'nullable|integer|min:0',
            'total_amount' => 'required|integer|min:0',
        ]);

        $session->update($data);

        return response()->json($session);
    }

    public function destroy(CashCounterSession $session): JsonResponse
    {
        if ($session->user_id !== auth()->id()) {
            abort(403);
        }

        $session->delete();

        return response()->json(['ok' => true]);
    }
}
