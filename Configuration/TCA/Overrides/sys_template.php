<?php
defined('TYPO3_MODE') or die();

call_user_func(function () {
    TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        'cleverreach',
        'Configuration/TypoScript/',
        'CleverReach'
    );
    TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        'cleverreach',
        'Configuration/TypoScript/Form/',
        'CleverReach Form'
    );
    TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        'cleverreach',
        'Configuration/TypoScript/Powermail/',
        'CleverReach Powermail'
    );

});

