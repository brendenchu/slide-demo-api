<?php

return [
    // Super Admin (also accessible as admin@demo.com for consistency with Vue SPA)
    'super_admin_name' => env('SUPER_ADMIN_NAME', 'Super Admin'),
    'super_admin_email' => env('SUPER_ADMIN_EMAIL', 'admin@demo.com'),
    'super_admin_password' => env('SUPER_ADMIN_PASSWORD', 'password'),

    // Admin
    'admin_name' => env('ADMIN_NAME', 'Admin User'),
    'admin_email' => env('ADMIN_EMAIL', 'admin@example.com'),
    'admin_password' => env('ADMIN_PASSWORD', 'password'),

    // Consultant
    'consultant_name' => env('CONSULTANT_NAME', 'Consultant User'),
    'consultant_email' => env('CONSULTANT_EMAIL', 'consultant@example.com'),
    'consultant_password' => env('CONSULTANT_PASSWORD', 'password'),

    // Client (also accessible as client@demo.com for consistency with Vue SPA)
    'client_name' => env('CLIENT_NAME', 'Client User'),
    'client_email' => env('CLIENT_EMAIL', 'client@demo.com'),
    'client_password' => env('CLIENT_PASSWORD', 'password'),

    // Guest
    'guest_name' => env('GUEST_NAME', 'Guest User'),
    'guest_email' => env('GUEST_EMAIL', 'guest@demo.com'),
    'guest_password' => env('GUEST_PASSWORD', 'password'),
];
