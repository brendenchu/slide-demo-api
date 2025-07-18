<?php

namespace App\Enums;

enum ProjectStatus: int
{
    case DRAFT = 1;
    case PUBLISHED = 2;
    case ARCHIVED = 3;
    case DELETED = 4;

    public function key(): string
    {
        return match ($this) {
            self::DRAFT => 'draft',
            self::PUBLISHED => 'published',
            self::ARCHIVED => 'archived',
            self::DELETED => 'deleted',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PUBLISHED => 'Published',
            self::ARCHIVED => 'Archived',
            self::DELETED => 'Deleted',
        };
    }
}
