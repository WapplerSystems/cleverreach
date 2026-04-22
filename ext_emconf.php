<?php
$EM_CONF['cleverreach'] = [
    'title' => 'CleverReach',
    'description' => 'Finishers and validators for EXT:form and Powermail',
    'category' => 'misc',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'author' => 'Sven Wappler',
    'author_email' => 'typo3YYYY@wappler.systems',
    'author_company' => 'WapplerSystems',
    'version' => '14.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '14.0.0-14.4.99',
            'oauth_service' => '14.0.0',
        ],
    ],
];
