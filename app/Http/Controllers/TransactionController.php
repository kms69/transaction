<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'payer' => 'required|string',
            'due_on' => 'required|date',
            'vat' => 'required|numeric',
            'is_vat_inclusive' => 'required|boolean',
        ]);

        $transactionData = $request->all() + [
                'status' => $this->calculateTransactionStatus($request->amount, $request->due_on),
                'user_id' => auth()->id(),
            ];
        // Save the transaction to the database
        $transaction = Transaction::create($transactionData);

        return response()->json(['transaction' => $transaction]);
    }

    private function calculateTransactionStatus($amount, $dueOn)
    {
        // Get the current date
        $currentDate = now();

        // Convert the due on date to a DateTime object
        $dueDate = \DateTime::createFromFormat('Y-m-d', $dueOn);

        // Compare the current date and due date to determine the status
        if ($currentDate >= $dueDate) {
            return 'Overdue';
        } elseif ($amount <= 0) {
            return 'Paid';
        } else {
            return 'Outstanding';
        }
    }

    public function index()
    {
        $user = Auth::user();

        // Check if the user is an admin
        if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
            // Admin can view all transactions
            $transactions = Transaction::all();
        } else {
            // Regular user can view only their transactions
            $transactions = $user->load('transactions')->transactions;
        }

        // Return the transactions data
        return response()->json([
            'user' => $user,
            'transactions' => $transactions,
        ]);
    }
}
