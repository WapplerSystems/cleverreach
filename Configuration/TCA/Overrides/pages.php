<?php
defined('TYPO3_MODE') or die();

/* Register PageTSconfig */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerPageTSConfigFile(
    'cleverreach',
    'Configuration/TSconfig/powermail.tsconfig',
    'EXT:cleverreach - Page TSconfig'
);
