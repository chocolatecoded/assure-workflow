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
        'APPROVAL' => 'Approval Form',
        'FORM_OPEN' => 'Form Open',
        'FORM_CLOSE' => 'Form Close',
        'PERMIT_OPEN' => 'Permit Open',
        'PERMIT_CLOSE' => 'Permit Close',
        'PRA' => 'PRA (default)',
    ],
    'virtual_steps' => [
        'FM_APPROVAL' => 'FM Approval',
        'END_FLOW' => 'End Flow',
    ],

    'controllers' => [
        'workflow' => \Assure\Workflow\Controllers\WorkflowController::class,
    ],
];

