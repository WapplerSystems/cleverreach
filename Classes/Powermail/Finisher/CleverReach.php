<?php
namespace WapplerSystems\Cleverreach\Powermail\Finisher;

/**
 * This file is part of the "cleverreach" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */


use In2code\Powermail\Domain\Model\Answer;
use In2code\Powermail\Domain\Model\Mail;
use In2code\Powermail\Finisher\AbstractFinisher;
use WapplerSystems\Cleverreach\CleverReach\Api;
use WapplerSystems\Cleverreach\Domain\Model\Receiver;

class CleverReach extends AbstractFinisher
{

    /**
     * @var \WapplerSystems\Cleverreach\CleverReach\Api
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $api;


    /**
     * @var \In2code\Powermail\Domain\Repository\MailRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $mailRepository;



    /**
     * Because of T3 7 compatibility use this class
     * @var \TYPO3\CMS\Extbase\Service\TypoScriptService
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $typoScriptService;


    /**
     * @var array
     */
    protected $dataArray = [];


    /**
     * @var string
     */
    protected $email = '';


    /**
     * @var string
     */
    protected $name = '';


    /**
     * @var \WapplerSystems\Cleverreach\Service\ConfigurationService
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $configurationService;


    /**
     *
     * @return void
     */
    public function cleverreachFinisher()
    {

        if ($this->email === '') return;

        $formValues = $this->getFormValues($this->getMail());

        $settings = $this->getSettings();
        $formId = isset($settings['main']['cleverreachFormId']) && \strlen($settings['main']['cleverreachFormId']) > 0 ? $settings['main']['cleverreachFormId'] : null;
        $groupId = isset($settings['main']['cleverreachGroupId']) && \strlen($settings['main']['cleverreachGroupId']) > 0 ? $settings['main']['cleverreachGroupId'] : null;


        if (array_key_exists('newslettercondition',$formValues)) {
            /* checkbox field exists -> check if true */
            if ((int)$formValues['newslettercondition'] !== 1) {
                return;
            }
        }

        if ($this->settings['main']['cleverreach'] === Api::MODE_OPTIN) {

            $receiver = new Receiver($this->email,$formValues);
            $this->api->addReceiversToGroup($receiver,$groupId);
            $this->api->sendSubscribeMail($this->email,$formId,$groupId);

        } else if ($this->settings['main']['cleverreach'] === Api::MODE_OPTOUT) {

            if ($this->configurationService->getUnsubscribeMethod() === 'doubleoptout') {

                $this->api->sendUnsubscribeMail($this->email);

            } else if ($this->configurationService->getUnsubscribeMethod() === 'delete') {

                $this->api->removeReceiversFromGroup($this->email);

            } else {

                $this->api->disableReceiversInGroup($this->email, $groupId);

            }

        }


    }



    /**
     * Initialize
     */
    public function initializeFinisher()
    {
        $configuration = $this->typoScriptService->convertPlainArrayToTypoScriptArray($this->settings);
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
