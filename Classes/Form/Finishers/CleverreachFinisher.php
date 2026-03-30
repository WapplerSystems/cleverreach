<?php
declare(strict_types=1);

namespace WapplerSystems\Cleverreach\Form\Finishers;


/**
 * This file is part of the "cleverreach" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */


use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;
use TYPO3\CMS\Form\Domain\Finishers\Exception\FinisherException;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use WapplerSystems\Cleverreach\CleverReach\Api;
use WapplerSystems\Cleverreach\Domain\Model\Receiver;
use WapplerSystems\Cleverreach\Service\ConfigurationService;
use WapplerSystems\OauthService\Crypto\CryptoService;
use WapplerSystems\OauthService\Domain\Repository\ConnectionRepository;


class CleverreachFinisher extends AbstractFinisher
{

    /**
     * @var array
     */
    protected $defaultOptions = [];

    public function __construct(
        private readonly ConnectionRepository $connectionRepository,
        private readonly CryptoService $cryptoService,
    ) {}

    /**
     * Executes this finisher
     * @throws FinisherException
     * @see AbstractFinisher::execute()
     *
     */
    protected function executeInternal(): void
    {

        $formValues = $this->getFormValues();

        /** @var ConfigurationService $configurationService */
        $configurationService = GeneralUtility::makeInstance(ConfigurationService::class);
        $configuration = $configurationService->getConfiguration();

        $api = GeneralUtility::makeInstance(Api::class);

        $clientUid = (int)($this->options['oauthClient'] ?? 0);
        if ($clientUid > 0) {
            $connection = $this->connectionRepository->findActiveConnectionByClientUid($clientUid);
            if ($connection === null) {
                throw new FinisherException('No active OAuth connection found for client #' . $clientUid . '.');
            }
            $accessToken = $this->cryptoService->decrypt($connection['access_token']);
            if ($accessToken === null || $accessToken === '') {
                throw new FinisherException('Active OAuth connection for client #' . $clientUid . ' has no valid access token.');
            }
            $api->connectWithToken($accessToken);
        }

        $listId = (int)(($this->options['listId'] ?? '') ? $this->options['listId'] : $configuration['listId']);
        $formId = ($this->options['formId'] ?? '') ? $this->options['formId'] : $configuration['formId'];

        if (empty($listId) || empty($formId)) {
            throw new FinisherException('Form ID or List ID not set.');
        }

        $email = null;
        $attributes = [];


        foreach ($formValues as $identifier => $value) {

            $element = $this->finisherContext->getFormRuntime()->getFormDefinition()->getElementByIdentifier($identifier);
            if ($element !== null) {
                $properties = $element->getProperties();
                if (isset($properties['cleverreachField'])) {
                    if ($properties['cleverreachField'] === 'email') {
                        $email = $value;
                    } else {
                        $attributes[$properties['cleverreachField']] = $value;
                    }
                }
            }
        }

        if (isset($this->options['mode']) && $email !== '') {

            if (strtolower($this->options['mode']) === Api::MODE_OPTIN) {

                $receiver = new Receiver($email, $attributes);
                $api->addReceiversToList($receiver, $listId);
                $api->sendSubscribeMail($email, $formId, $listId);

            } else if (strtolower($this->options['mode']) === Api::MODE_OPTOUT) {

                $api->sendUnsubscribeMail($email, $formId, $listId);

            }

        }
    }


    /**
     * Returns the values of the submitted form
     *
     * @return array
     */
    protected function getFormValues(): array
    {
        return $this->finisherContext->getFormValues();
    }

    /**
     * Returns a form element object for a given identifier.
     *
     * @param string $elementIdentifier
     * @return NULL|FormElementInterface
     */
    protected function getElementByIdentifier(string $elementIdentifier)
    {
        return $this
            ->finisherContext
            ->getFormRuntime()
            ->getFormDefinition()
            ->getElementByIdentifier($elementIdentifier);
    }


}