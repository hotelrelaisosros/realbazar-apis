<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\Order;

class InvoiceController extends Controller
{
    public function generateInvoiceId($id)
    {
        $order = Order::with(['user_orders.products', 'user_orders.variation', 'user_payments.payments'])->where('id', $id);

        $pdf = Pdf::loadView('pdf.invoice', compact('order'));

        return $pdf->stream('invoice_' . $id . '.pdf');
    }

    public function generateReceiptId($id)
    {
        $order = Order::with(['user_orders.products', 'user_orders.variation', 'user_payments.payments'])->where(
            'id',
            $id
        );

        $pdf = Pdf::loadView('pdf.receipts', compact('order'));

        return $pdf->download('receipt_' . $id . '.pdf');
    }

    public function generateInvoicepId($id)
    {
        $order = Order::with([
            'user_orders.products',
            'user_orders.variation',
            'user_payments.payments'
        ])
            ->whereHas('user_payments.payments', function ($query) use ($id) {
                $query->where('txt_refno', $id);
            })
            ->first();
        $pdf = Pdf::loadView('pdf.invoice', compact('order'));

        return $pdf->stream('invoice_' . $id . '.pdf');
    }

    public function generateReceiptpId($id)
    {
        $order = Order::with([
            'user_orders.products',
            'user_orders.variation',
            'user_payments.payments'
        ])
            ->whereHas('user_payments.payments', function ($query) use ($id) {
                $query->where('txt_refno', $id);
            })
            ->first();

        $pdf = Pdf::loadView('pdf.receipts', compact('order'));

        return $pdf->download('receipt_' . $id . '.pdf');
    }
}
