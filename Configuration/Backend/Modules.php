<?php


use WapplerSystems\Cleverreach\Controller\Backend\CleverreachModuleController;

return [
    'system_cleverreach' => [
        'parent' => 'system',
        'position' => [],
        'access' => 'admin',
        'workspaces' => 'online',
        'path' => '/module/system/cleverreach',
        'iconIdentifier' => 'cleverreach-module',
        'labels' => 'LLL:EXT:cleverreach/Resources/Private/Language/locallang_mod.xlf',
        'routes' => [
            '_default' => [
                'target' => CleverreachModuleController::class . '::handleRequest',
            ],
        ],
    ],
];
