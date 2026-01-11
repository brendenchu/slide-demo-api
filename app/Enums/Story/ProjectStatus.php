<?php

namespace App\Enums\Story;

enum ProjectStatus: int
{
    case Draft = 1;
    case InProgress = 2;
    case Completed = 3;
    case Published = 4;
    case Archived = 5;
    case Deleted = 6;

    public function key(): string
    {
        return match ($this) {
            self::Draft => 'draft',
            self::InProgress => 'in_progress',
            self::Completed => 'completed',
            self::Published => 'published',
            self::Archived => 'archived',
            self::Deleted => 'deleted',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::InProgress => 'In Progress',
            self::Completed => 'Completed',
            self::Published => 'Published',
            self::Archived => 'Archived',
            self::Deleted => 'Deleted',
        };
    }
}
