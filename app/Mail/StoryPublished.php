<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StoryPublished extends Mailable
{
    use Queueable, SerializesModels;

    private array $submitter;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user,
    ) {
        $this->submitter = [
            'name' => $this->user->profile->first_name . ' ' . $this->user->profile->last_name,
            'email' => $this->user->email,
        ];
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Client Form Submission',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.story-published',
            with: [
                'submitter' => $this->submitter,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
