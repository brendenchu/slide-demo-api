<?php

namespace App\Enums;

enum ProjectStep: string
{
    case STEP_ZERO = 'intro';
    case STEP_ONE = 'section-a';
    case STEP_TWO = 'section-b';
    case STEP_THREE = 'section-c';
    case COMPLETE = 'complete';

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
            self::STEP_ZERO => 0,
            self::STEP_ONE => 1,
            self::STEP_TWO => 2,
            self::STEP_THREE => 3,
            self::COMPLETE => 4,
        };
    }

    public function slug(): string
    {
        return match ($this) {
            self::STEP_ZERO => 'intro',
            self::STEP_ONE => 'section-a',
            self::STEP_TWO => 'section-b',
            self::STEP_THREE => 'section-c',
            self::COMPLETE => 'complete',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::STEP_ZERO => 'Introduction',
            self::STEP_ONE => 'Section A',
            self::STEP_TWO => 'Section B',
            self::STEP_THREE => 'Section C',
            self::COMPLETE => 'Complete',
        };
    }

    public function fields(): array
    {
        return match ($this) {
            self::STEP_ZERO => [
                'intro_1',
                'intro_2',
                'intro_3',
            ],
            self::STEP_ONE => [
                'section_a_1',
                'section_a_2',
                'section_a_3',
                'section_a_4',
                'section_a_5',
                'section_a_6',
            ],
            self::STEP_TWO => [
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
            self::STEP_THREE => [
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
            self::COMPLETE => [],
        };
    }
}
