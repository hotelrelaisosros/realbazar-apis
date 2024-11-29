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

class ReceiptMail extends Mailable
{
    use Queueable, SerializesModels;
    protected $order;
    protected $receiptUrl;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($order, $receiptUrl)
    {
        if (!$order) {
            Log::error('Order is null in ReceiptMail.');
        }
        $this->order = $order;
        $this->receiptUrl = $receiptUrl;
    }

    /**
     * Define the email envelope.
     *
     * @return Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Your Receipt for Order #',
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
            view: 'email.receipts', // Blade file for the email body
            with: [
                'order' => $this->order,
                'receiptUrl' => $this->receiptUrl,
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
                'receiptUrl' => $this->receiptUrl,
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
