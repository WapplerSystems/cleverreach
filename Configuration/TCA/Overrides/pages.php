<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerPageTSConfigFile(
    'cleverreach',
    'Configuration/TsConfig/Page/powermail.tsconfig',
    'Powermail'
);
