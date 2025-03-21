<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Dompdf\Dompdf;

class RefundNotificationMail extends Mailable
{
    use Queueable, SerializesModels;
    protected $order;
    protected $receiptUrl;


    public function __construct($order)
    {
        if (!$order) {
            Log::error('Order is ' . $order . ' in ReceiptMail.');
        }
        $this->order = $order;
        // $this->receiptUrl = $receiptUrl;
    }

    /**
     * Define the email envelope.
     *
     * @return Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Your Refund for Order #',
        );
    }

    /**
     * Define the email content.
     *
     * @return Content
     */
    public function content()
    {
        Log::info("order is " . $this->order);

        return new Content(
            view: 'email.receipts',
            with: [
                'order' => $this->order,
                // 'receiptUrl' => $this->receiptUrl,
            ]
        );
    }

    /**
     * Attach the PDF receipt.
     *
     * @return array
     */
    public function attachments()
    {
        // Generate the PDF using dompdf
        try {

            $pdf = Pdf::loadView('pdf.receipts', [
                'order' => $this->order,
                // 'receiptUrl' => $this->receiptUrl,
            ]);

            return [

                Attachment::fromData(
                    fn() => $pdf->output(),
                    'receipt.pdf'
                )->withMime('application/pdf'),
            ];
        } catch (\Exception $e) {
            Log::error('PDF generation failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
