<?php

namespace App\Mail;

use App\Models\Ppsmb;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PpsmbReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public Ppsmb $ppsmb;
    public int $hariKe;
    
    public function __construct(Ppsmb $ppsmb, int $hariKe)
    {
        $this->ppsmb = $ppsmb;
        $this->hariKe = $hariKe;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reminder Revisi PPSMB - ' . $this->ppsmb->nama_project,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'Email.ppsmbreminder',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
