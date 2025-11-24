<?php


declare(strict_types=1);

return [
    'backend' => [
        'cleverreach/oauth/callback' => [
            'target' => \WapplerSystems\Cleverreach\Middleware\CleverreachOauthCallbackMiddleware::class,
            'before' => [
                'typo3/cms-backend/locked-backend',
            ],
        ],
    ],
];
