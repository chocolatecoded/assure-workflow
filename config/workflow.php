<?php

return [
    'default' => [
        'engine' => 'basic',
        'storage' => 'database',
    ],
    
    // Dashboard tile configuration
    'show_dashboard_tile' => env('WORKFLOW_SHOW_DASHBOARD_TILE', true),
    'dashboard_tile_roles' => ['super_admin'], // Roles that can see the tile,
    'step_types' => [
        'PRA' => 'PRA (default)',
        'Closure' => 'Closure',
    ],
];

