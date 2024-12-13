<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$boot = static function (): void {


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

};

$boot();
unset($boot);
