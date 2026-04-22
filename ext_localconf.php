<?php

use TYPO3\CMS\Core\Utility\GeneralUtility;
use WapplerSystems\OauthService\Provider\ProviderDefinition;
use WapplerSystems\OauthService\Provider\ProviderRegistryInterface;

(static function () {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['afterSubmit'][] =
        \WapplerSystems\Cleverreach\Form\Hook\AfterSubmitHook::class;

    $registry = GeneralUtility::makeInstance(ProviderRegistryInterface::class);
    $registry->register(new ProviderDefinition(
        identifier: 'cleverreach',
        title: 'CleverReach OAuth',
        type: 'generic_oauth2',
        authorizationUrl: 'https://rest.cleverreach.com/oauth/authorize.php',
        tokenUrl: 'https://rest.cleverreach.com/oauth/token.php',
    ));
})();
