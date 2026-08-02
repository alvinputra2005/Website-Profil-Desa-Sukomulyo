<?php

return [
    'population_year' => (int) env('VILLAGE_POPULATION_YEAR', now()->year),
    'letter_service' => [
        'whatsapp_number' => env('VILLAGE_WHATSAPP_NUMBER', ''),
        'office_hours' => env('LETTER_OFFICE_HOURS', 'Senin-Jumat, 08.00-14.00 WIB'),
        'pickup_address' => env('LETTER_PICKUP_ADDRESS', 'Kantor Desa Sukomulyo'),
        'tracking_retention_days' => (int) env('LETTER_TRACKING_RETENTION_DAYS', 90),
        'enabled' => (bool) env('LETTER_SERVICE_ENABLED', true),
    ],
];
