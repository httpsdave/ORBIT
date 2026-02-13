<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactFeedback extends Mailable
{
    use Queueable, SerializesModels;

    public string $senderName;
    public string $senderEmail;
    public string $feedbackSubject;
    public string $feedbackMessage;
    public string $category;

    public function __construct(string $senderName, string $senderEmail, string $feedbackSubject, string $feedbackMessage, string $category)
    {
        $this->senderName = $senderName;
        $this->senderEmail = $senderEmail;
        $this->feedbackSubject = $feedbackSubject;
        $this->feedbackMessage = $feedbackMessage;
        $this->category = $category;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[ORBIT Feedback] {$this->feedbackSubject}",
            replyTo: [$this->senderEmail],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-feedback',
        );
    }
}
