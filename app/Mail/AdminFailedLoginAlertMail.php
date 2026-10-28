<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminFailedLoginAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $ip,
        public ?string $attemptedEmail,
        public string $location,
        public ?string $userAgent,
        public int $attempts,
        public string $blockUrl,
        public string $timestamp
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "🚨 [Security Alert] {$this->attempts} Failed Admin Login Attempts from IP: {$this->ip}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-failed-login-alert',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

