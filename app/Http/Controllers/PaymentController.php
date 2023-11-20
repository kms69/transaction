<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    protected $amount;

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required|exists:transactions,id',
            'amount' => 'required|numeric',
            'paid_on' => 'required|date',
            'details' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $payment = Payment::create($request->all());

        // Update transaction status based on payment
        $this->updateTransactionStatus($payment->transaction);

        return response()->json($payment, 201);
    }

    private function updateTransactionStatus($transaction)
    {
        // Calculate the total paid amount for the transaction
        $totalPaid = $transaction->payments()->sum('amount');

        // Update transaction status based on payments and totalPaid
        $transaction->status = $this->calculateTransactionStatus($transaction->due_on, $totalPaid);

        // Save the updated transaction status
        $transaction->save();
    }

    private function calculateTransactionStatus($dueDate, $totalPaid)
    {
        $currentDate = now();

        if ($totalPaid >= $this->getTotalAmount()) {
            return 'Paid';
        } elseif ($currentDate <= $dueDate && $totalPaid > 0) {
            return 'Outstanding';
        } elseif ($currentDate > $dueDate && $totalPaid < $this->getTotalAmount()) {
            return 'Overdue';
        }

        return 'Outstanding';
    }

    private function getTotalAmount()
    {

        return $this->amount;
    }

}
