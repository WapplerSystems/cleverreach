<?php
declare(strict_types=1);

namespace WapplerSystems\Cleverreach\Form\Finishers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;
use TYPO3\CMS\Form\Domain\Finishers\Exception\FinisherException;
use WapplerSystems\Cleverreach\CleverReach\Api;
use WapplerSystems\Cleverreach\Domain\Model\Receiver;
use WapplerSystems\Cleverreach\Service\CleverreachFormContext;
use WapplerSystems\OauthService\Service\OAuthClientService;

class CleverreachFinisher extends AbstractFinisher
{
    protected $defaultOptions = [];

    public function __construct(
        private readonly OAuthClientService    $oAuthClientService,
        private readonly CleverreachFormContext $context,
    )
    {
    }

    /**
     * Called by EXT:form when the form definition is built — before validators run.
     * Populates CleverreachFormContext so AfterSubmitHook, OptinValidator and
     * OptoutValidator can access groupId and oauthClient.
     */
    public function setOptions(array $options): void
    {
        parent::setOptions($options);
        $this->context->setSettings($this->options);
    }

    protected function executeInternal(): void
    {
        $api = GeneralUtility::makeInstance(Api::class);

        $groupId = (int)$this->parseOption('groupId');
        $formId = (int)$this->parseOption('formId');
        $mode = strtolower($this->parseOption('mode'));

        if (empty($groupId) || empty($formId)) {
            throw new FinisherException('Form ID or group ID not set.');
        }

        $email = $this->parseOption('emailField');
        $attributes = [];

        $fields = $this->finisherContext->getFormRuntime()->getFormDefinition()->getElements();
        foreach ($fields as $field) {
            if ($field->getProperties()['cleverreachField'] ?? false) {
                $attributes[$field->getProperties()['cleverreachField']] = $this->getFormValues()[$field->getIdentifier()] ?? '';
            }
        }

        if (!empty($mode) && !empty($email)) {
            if ($mode === Api::MODE_OPTIN) {
                $receiver = new Receiver($email, $attributes);
                $api->addReceiversToList($receiver, $groupId);
                $api->sendSubscribeMail($email, $formId, $groupId);
            } elseif ($mode === Api::MODE_OPTOUT) {
                $api->sendUnsubscribeMail($email, $formId, $groupId);
            }
        }
    }

    protected function getFormValues(): array
    {
        return $this->finisherContext->getFormValues();
    }
}
