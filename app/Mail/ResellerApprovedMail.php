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
            subject: '🎉 Congratulations! Your Reseller Account Has Been Approved',
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
