<?php
namespace WapplerSystems\Cleverreach\Powermail\Finisher;

use In2code\Powermail\Domain\Model\Answer;
use In2code\Powermail\Domain\Model\Mail;
use In2code\Powermail\Finisher\AbstractFinisher;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use WapplerSystems\Cleverreach\CleverReach\Api;
use WapplerSystems\Cleverreach\Domain\Model\Receiver;
use WapplerSystems\Cleverreach\Service\ConfigurationService;
use WapplerSystems\OauthService\Crypto\CryptoService;
use WapplerSystems\OauthService\Domain\Repository\ConnectionRepository;

class CleverReach extends AbstractFinisher
{

    private ConnectionRepository $connectionRepository;
    private CryptoService $cryptoService;

    public function __construct(
        Mail $mail,
        array $configuration,
        array $settings,
        bool $formSubmitted,
        string $actionMethodName,
        ContentObjectRenderer $contentObject,
        ?ConnectionRepository $connectionRepository = null,
        ?CryptoService $cryptoService = null,
    ) {
        parent::__construct($mail, $configuration, $settings, $formSubmitted, $actionMethodName, $contentObject);
        $this->connectionRepository = $connectionRepository ?? GeneralUtility::makeInstance(ConnectionRepository::class);
        $this->cryptoService = $cryptoService ?? GeneralUtility::makeInstance(CryptoService::class);
    }

    /**
     * @var array
     */
    protected array $dataArray = [];


    /**
     * @var string
     */
    protected string $email = '';


    /**
     * @var string
     */
    protected string $name = '';


    /**
     *
     * @return void
     */
    public function cleverreachFinisher(): void
    {

        if ($this->email === '') return;

        $api = GeneralUtility::makeInstance(Api::class);

        $formValues = $this->getFormValues($this->getMail());

        $settings = $this->getSettings();

        $clientUid = (int)($settings['main']['cleverreachClientUid'] ?? 0);
        if ($clientUid > 0) {
            $connection = $this->connectionRepository->findActiveConnectionByClientUid($clientUid);
            if ($connection !== null) {
                $accessToken = $this->cryptoService->decrypt($connection['access_token']);
                if ($accessToken !== null && $accessToken !== '') {
                    $api->connectWithToken($accessToken);
                }
            }
        }
        $formId = isset($settings['main']['cleverreachFormId']) && \strlen($settings['main']['cleverreachFormId']) > 0 ? $settings['main']['cleverreachFormId'] : null;
        $groupId = isset($settings['main']['cleverreachListId']) && \strlen($settings['main']['cleverreachListId']) > 0 ? $settings['main']['cleverreachListId'] : null;


        if (array_key_exists('newslettercondition',$formValues)) {
            /* checkbox field exists -> check if true */
            if ((int)$formValues['newslettercondition'] !== 1) {
                return;
            }
        }

        if ($this->settings['main']['cleverreach'] === Api::MODE_OPTIN) {

            $receiver = new Receiver($this->email,$formValues);
            $api->addReceiversToList($receiver,(int)$groupId);
            $api->sendSubscribeMail($this->email,$formId,$groupId);

        } else if ($this->settings['main']['cleverreach'] === Api::MODE_OPTOUT) {

            if ($settings['main']['cleverreachUnsubscribeMethod'] === 'doubleoptout') {

                $api->sendUnsubscribeMail($this->email);

            } else if ($settings['main']['cleverreachUnsubscribeMethod'] === 'delete') {

                $api->removeReceiversFromGroup($this->email);

            } else {

                $api->disableReceiversInGroup($this->email, $groupId);

            }

        }


    }



    /**
     * Initialize
     */
    public function initializeFinisher(): void
    {
        $typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);
        $configuration = $typoScriptService->convertPlainArrayToTypoScriptArray($this->settings);
        if (!empty($configuration['dbEntry.'])) {
            $this->configuration = $configuration['dbEntry.'];
        }

        $this->email = $this->findSenderEmail($this->mail);
    }


    /**
     * @param Mail $mail
     * @return array
     */
    private function getFormValues(Mail $mail) {
        $values = [];

        /** @var Answer $answer */
        foreach ($mail->getAnswers() as $answer) {

            if (!method_exists($answer, 'getField') || !method_exists($answer->getField(), 'getMarker')) {
                continue;
            }

            $value = $answer->getValue();
            if (\is_array($value)) {
                $value = implode(', ', $value);
            }

            $values[$answer->getField()->getMarker()] = $value;

        }

        return $values;
    }

    /**
     *
     * @param Mail $mail
     * @return string
     */
    private function findSenderEmail(Mail $mail): string
    {
        /** @var Answer $answer */
        foreach ($mail->getAnswers() as $answer) {
            if (!method_exists($answer, 'getField') || !method_exists($answer->getField(), 'getMarker')) {
                continue;
            }
            $value = $answer->getValue();
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            if ($answer->getField()->isSenderEmail()) {
                return $value;
            }
        }

        return '';
    }


    /**
     *
     * @param Mail $mail
     * @return string
     */
    private function findSenderName(Mail $mail): string
    {
        /** @var Answer $answer */
        foreach ($mail->getAnswers() as $answer) {
            if (!method_exists($answer, 'getField') || !method_exists($answer->getField(), 'getMarker')) {
                continue;
            }
            $value = $answer->getValue();
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            if ($answer->getField()->isSenderName()) {
                return $value;
            }
        }

        return '';
    }

}
