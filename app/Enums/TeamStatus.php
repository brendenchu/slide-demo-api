<?php

namespace App\Enums;

enum TeamStatus: int
{
    case ACTIVE = 1;
    case INACTIVE = 2;
    case DELETED = 3;

    public function key(): string
    {
        return match ($this) {
            self::ACTIVE => 'active',
            self::INACTIVE => 'inactive',
            self::DELETED => 'deleted',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::DELETED => 'Deleted',
        };
    }
}
