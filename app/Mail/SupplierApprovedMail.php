<?php

namespace App\Mail;

use App\Models\Supplier;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupplierApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Supplier $supplier)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎉 অভিনন্দন! আপনার সাপ্লায়ার অ্যাকাউন্ট অনুমোদিত হয়েছে — ব্যবসা শুরু করুন',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.supplier-approved',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
