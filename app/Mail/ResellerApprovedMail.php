<?php

namespace App\Mail;

use App\Models\Reseller;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResellerApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Reseller $reseller)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎉 অভিনন্দন! আপনার রিসেলার অ্যাকাউন্ট অনুমোদিত হয়েছে — ব্যবসা শুরু করুন',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reseller-approved',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
