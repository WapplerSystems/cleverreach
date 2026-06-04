<?php

use WapplerSystems\Cleverreach\Form\Hook\AfterSubmitHook;

(static function () {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['afterSubmit'][] = AfterSubmitHook::class;
})();

// Provider registration lives in Configuration/Services.yaml as a tagged
// service (tag: oauth_service.provider_definition) so the provider is also
// available in the TYPO3 Install Tool's failsafe bootstrap, where
// ext_localconf.php is not executed.
