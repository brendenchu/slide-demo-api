<?php

namespace App\Enums\Account;

enum InvitationStatus: int
{
    case Pending = 1;
    case Accepted = 2;
    case Declined = 3;
    case Cancelled = 4;

    public function key(): string
    {
        return match ($this) {
            self::Pending => 'pending',
            self::Accepted => 'accepted',
            self::Declined => 'declined',
            self::Cancelled => 'cancelled',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Accepted => 'Accepted',
            self::Declined => 'Declined',
            self::Cancelled => 'Cancelled',
        };
    }
}
