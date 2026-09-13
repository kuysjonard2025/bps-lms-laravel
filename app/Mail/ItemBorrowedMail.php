<?php

namespace App\Mail;

use App\Models\Circulation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ItemBorrowedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Circulation $circulation;

    public function __construct(Circulation $circulation)
    {
        $this->circulation = $circulation;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Library Book Borrowed Successfully',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.circulations.borrowed', // points to resources/views/emails/circulations/borrowed.blade.php
        );
    }
}
