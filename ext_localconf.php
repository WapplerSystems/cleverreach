<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WapplerSystems\OauthService\Provider\ProviderDefinition;
use WapplerSystems\OauthService\Provider\ProviderRegistryInterface;

(static function () {
    $registry = GeneralUtility::makeInstance(ProviderRegistryInterface::class);
    $registry->register(new ProviderDefinition(
        identifier: 'cleverreach',
        title: 'CleverReach OAuth',
        type: 'generic_oauth2',
        authorizationUrl: 'https://rest.cleverreach.com/oauth/authorize.php',
        tokenUrl: 'https://rest.cleverreach.com/oauth/token.php',
    ));


    ExtensionManagementUtility::addPageTSConfig(
        "@import 'EXT:cleverreach/Configuration/TsConfig/powermail.tsconfig'"
    );

    ExtensionManagementUtility::addTypoScriptSetup(
        'module.tx_form {
    settings {
        yamlConfigurations {
            43646 = EXT:cleverreach/Configuration/Yaml/BaseSetup.yaml
        }
    }
}'
    );

})();

