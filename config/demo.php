<?php

return [
    // Demo mode
    'enabled' => env('DEMO_MODE', false),

    // Resource limits (enforced when demo mode is enabled)
    'limits' => [
        'max_users' => (int) env('DEMO_MAX_USERS', 25),
        'max_teams_per_user' => (int) env('DEMO_MAX_TEAMS_PER_USER', 3),
        'max_projects_per_team' => (int) env('DEMO_MAX_PROJECTS_PER_TEAM', 5),
        'max_invitations_per_team' => (int) env('DEMO_MAX_INVITATIONS_PER_TEAM', 5),
    ],

    // Demo user credentials
    'demo_user_name' => env('DEMO_USER_NAME', 'Demo User'),
    'demo_user_email' => env('DEMO_USER_EMAIL', 'demo@example.com'),
    'demo_user_password' => env('DEMO_USER_PASSWORD', 'password'),
];
