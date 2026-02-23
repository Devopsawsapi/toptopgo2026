<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index(Request $request)
    {
        $query = Transaction::with('user')
            ->where('type', 'withdrawal');

        // Filter by status
        $status = $request->get('status', 'pending');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $withdrawals = $query->latest()->paginate(20);

        $stats = [
            'pending' => Transaction::where('type', 'withdrawal')
                ->where('status', 'pending')
                ->count(),
            'pending_amount' => Transaction::where('type', 'withdrawal')
                ->where('status', 'pending')
                ->sum('amount'),
            'processed_today' => Transaction::where('type', 'withdrawal')
                ->where('status', 'completed')
                ->whereDate('updated_at', today())
                ->sum('amount'),
        ];

        return view('admin.withdrawals.index', compact('withdrawals', 'stats', 'status'));
    }

    public function process(Transaction $transaction)
    {
        if ($transaction->type !== 'withdrawal' || $transaction->status !== 'pending') {
            return back()->with('error', 'Transaction invalide');
        }

        try {
            // Process the withdrawal through payment service
            $result = $this->paymentService->processWithdrawal($transaction);

            if ($result['success']) {
                return back()->with('success', 'Retrait traité avec succès');
            }

            return back()->with('error', $result['message'] ?? 'Erreur lors du traitement');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, Transaction $transaction)
    {
        if ($transaction->type !== 'withdrawal' || $transaction->status !== 'pending') {
            return back()->with('error', 'Transaction invalide');
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $transaction->update([
            'status' => 'failed',
            'metadata' => array_merge($transaction->metadata ?? [], [
                'rejection_reason' => $request->reason,
                'rejected_at' => now()->toISOString(),
                'rejected_by' => auth()->id(),
            ]),
        ]);

        // Refund the amount to driver's wallet
        $wallet = $transaction->user->wallets()->where('currency', 'XAF')->first();
        if ($wallet) {
            $wallet->increment('balance', $transaction->amount);
        }

        // Notify driver
        // $transaction->user->notify(new WithdrawalRejected($transaction, $request->reason));

        return back()->with('success', 'Retrait rejeté');
    }
}
