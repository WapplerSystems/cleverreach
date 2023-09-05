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
    'version' => '12.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-12.4.99',
            'php' => '8.2.0-8.2.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
