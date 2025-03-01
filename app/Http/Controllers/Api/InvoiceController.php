<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\Order;

class InvoiceController extends Controller
{
    public function generateInvoice($id)
    {
        $order = Order::with(['user_orders.products', 'user_orders.variation', 'user_payments.payments'])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.invoice', compact('order'));

        return $pdf->stream('invoice_' . $id . '.pdf');
    }

    public function generateReceipt($id)
    {
        $order = Order::with(['user_orders.products', 'user_orders.variation', 'user_payments.payments'])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.receipt', compact('order'));

        return $pdf->download('receipt_' . $id . '.pdf');
    }
}
