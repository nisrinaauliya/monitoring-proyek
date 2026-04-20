<?php

namespace App\Mail;

use App\Models\Ppsmb;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PpsmbRejectMail extends Mailable
{
    use Queueable, SerializesModels;

    public Ppsmb $ppsmb;

    public function __construct(Ppsmb $ppsmb)
    {
        $this->ppsmb = $ppsmb;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'PPSMB Otomatis Direject - ' . $this->ppsmb->nama_project,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'Email.ppsmbreject',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
