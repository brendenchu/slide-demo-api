<?php

return [

    'terms_version' => '1.0.0',

    'current_version' => 1,

    'versions' => [
        1 => [
            'label' => 'Agreement of Service',
            'url' => 'https://example.com/terms',
            'required' => true,
            'required_for' => [
                'user' => true,
                'team' => true,
            ],
        ],
    ],

    'strikes' => [
        'user' => [
            'max' => 3,
            'expires' => 30,
        ],
        'team' => [
            'max' => 3,
            'expires' => 30,
        ],
    ],

];
