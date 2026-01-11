<?php

namespace App\Enums\Story;

enum ProjectStep: string
{
    case Intro = 'intro';
    case SectionA = 'section-a';
    case SectionB = 'section-b';
    case SectionC = 'section-c';
    case Complete = 'complete';

    public static function allSteps(): array
    {
        return [
            'intro' => 'Introduction',
            'section-a' => 'Section A',
            'section-b' => 'Section B',
            'section-c' => 'Section C',
        ];
    }

    public function key(): int
    {
        return match ($this) {
            self::Intro => 0,
            self::SectionA => 1,
            self::SectionB => 2,
            self::SectionC => 3,
            self::Complete => 4,
        };
    }

    public function slug(): string
    {
        return match ($this) {
            self::Intro => 'intro',
            self::SectionA => 'section-a',
            self::SectionB => 'section-b',
            self::SectionC => 'section-c',
            self::Complete => 'complete',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Intro => 'Introduction',
            self::SectionA => 'Section A',
            self::SectionB => 'Section B',
            self::SectionC => 'Section C',
            self::Complete => 'Complete',
        };
    }

    public function fields(): array
    {
        return match ($this) {
            self::Intro => [
                'intro_1',
                'intro_2',
                'intro_3',
            ],
            self::SectionA => [
                'section_a_1',
                'section_a_2',
                'section_a_3',
                'section_a_4',
                'section_a_5',
                'section_a_6',
            ],
            self::SectionB => [
                'section_b_1',
                'section_b_2',
                'section_b_3',
                'section_b_4',
                'section_b_5',
                'section_b_6',
                'section_b_7',
                'section_b_8',
                'section_b_9',
            ],
            self::SectionC => [
                'section_c_1',
                'section_c_2',
                'section_c_3',
                'section_c_4',
                'section_c_5',
                'section_c_6',
                'section_c_7',
                'section_c_8',
                'section_c_9',
            ],
            self::Complete => [],
        };
    }
}
