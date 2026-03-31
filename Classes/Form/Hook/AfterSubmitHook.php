<?php
declare(strict_types=1);

namespace WapplerSystems\Cleverreach\Form\Hook;

use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use WapplerSystems\Cleverreach\CleverReach\Api;
use WapplerSystems\Cleverreach\Form\Finishers\CleverreachFinisher;
use WapplerSystems\Cleverreach\Service\CleverreachFormContext;
use WapplerSystems\OauthService\Service\OAuthClientService;

/**
 * EXT:form afterSubmit hook.
 *
 * Fires for each form element during mapAndValidatePage(), before field
 * validators run. On the first invocation per request it reads the
 * oauthClient UID from CleverreachFormContext (populated earlier by
 * CleverreachFinisher::setOptions()) and authenticates the Api singleton
 * via OAuthClientService so that OptinValidator / OptoutValidator can
 * call the CleverReach API without establishing their own connection.
 */
final class AfterSubmitHook
{
    private bool $connected = false;

    public function __construct(
        private readonly CleverreachFormContext $context,
        private readonly OAuthClientService $oAuthClientService,
        private readonly Api $api,
    ) {}

    public function afterSubmit(
        FormRuntime $formRuntime,
        mixed $renderable,
        mixed $value,
        array $requestArguments
    ): mixed {

        $finishers = $formRuntime->getFormDefinition()->getFinishers();
        $found = false;
        foreach ($finishers as $finisher) {
            if ($finisher instanceof CleverreachFinisher) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            return $value;
        }

        if (!$this->connected && $this->context->isInitialized()) {
            $clientUid = (int)$this->context->get('oauthClient', 0);
            if ($clientUid > 0) {
                $connection = $this->oAuthClientService->getActiveConnectionByClientUid($clientUid);
                if ($connection !== null && $connection['access_token'] !== '') {
                    $this->api->connectWithToken($connection['access_token']);
                }
            }
            $this->connected = true;
        }

        return $value;
    }
}
