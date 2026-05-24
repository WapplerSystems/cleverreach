<?php
declare(strict_types=1);

namespace WapplerSystems\Cleverreach\Form\Finishers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;
use TYPO3\CMS\Form\Domain\Finishers\Exception\FinisherException;
use WapplerSystems\Cleverreach\CleverReach\Api;
use WapplerSystems\Cleverreach\Service\CleverreachFormContext;
use WapplerSystems\OauthService\Service\OAuthClientService;

class CleverreachOptOutFinisher extends AbstractFinisher
{
    protected $defaultOptions = [];

    public function __construct(
        private readonly OAuthClientService    $oAuthClientService,
        private readonly CleverreachFormContext $context,
    ) {}

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

        if (empty($groupId) || empty($formId)) {
            throw new FinisherException('Form ID or group ID not set.');
        }

        $email = $this->parseOption('emailField');
        if (empty($email)) {
            return;
        }

        $api->sendUnsubscribeMail($email, $formId, $groupId);
    }
}