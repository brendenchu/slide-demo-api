<?php

namespace App\Notifications;

use App\Models\Account\TeamInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public TeamInvitation $invitation) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $teamName = $this->invitation->team->label;
        $inviterName = $this->invitation->invitedBy->name;
        $acceptUrl = config('app.frontend_url', config('app.url'))
            . '/invitations?token=' . $this->invitation->token;

        return (new MailMessage)
            ->subject("You've been invited to join {$teamName}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("{$inviterName} has invited you to join **{$teamName}** as a {$this->invitation->role}.")
            ->action('View Invitation', $acceptUrl)
            ->line('This invitation will expire in 7 days.');
    }
}
