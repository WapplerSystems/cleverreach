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
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WapplerSystems\Cleverreach\CleverReach\Api;
use WapplerSystems\Cleverreach\Domain\Model\Receiver;
use WapplerSystems\Cleverreach\Service\ConfigurationService;

class CleverReach extends AbstractFinisher
{

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
     *
     * @return void
     */
    public function cleverreachFinisher()
    {

        if ($this->email === '') return;

        $api = GeneralUtility::makeInstance(Api::class);
        $configurationService = GeneralUtility::makeInstance(ConfigurationService::class);

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
            $api->addReceiversToGroup($receiver,$groupId);
            $api->sendSubscribeMail($this->email,$formId,$groupId);

        } else if ($this->settings['main']['cleverreach'] === Api::MODE_OPTOUT) {

            if ($configurationService->getUnsubscribeMethod() === 'doubleoptout') {

                $api->sendUnsubscribeMail($this->email);

            } else if ($configurationService->getUnsubscribeMethod() === 'delete') {

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
