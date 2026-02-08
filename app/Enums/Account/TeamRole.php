<?php

namespace App\Enums\Account;

enum TeamRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Member = 'member';

    /**
     * Roles that can be assigned via invitation or role update.
     *
     * @return array<string>
     */
    public static function assignable(): array
    {
        return [
            self::Admin->value,
            self::Member->value,
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Admin => 'Admin',
            self::Member => 'Member',
        };
    }

    /**
     * Whether this role has admin-level privileges.
     */
    public function isAdminLevel(): bool
    {
        return match ($this) {
            self::Owner, self::Admin => true,
            self::Member => false,
        };
    }
}
