<?php

namespace WapplerSystems\Cleverreach\CleverReach;

/**
 * This file is part of the "cleverreach" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */


use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WapplerSystems\Cleverreach\Domain\Model\Receiver;
use WapplerSystems\Cleverreach\Service\ConfigurationService;
use WapplerSystems\Cleverreach\Tools\Rest;


class Api
{

    /**
     * @var ConfigurationService
     */
    protected ConfigurationService $configurationService;


    /** @var Rest */
    protected $rest;


    /** @var \TYPO3\CMS\Core\Log\Logger */
    protected $logger;


    public const MODE_OPTIN = 'optin';

    public const MODE_OPTOUT = 'optout';


    public function __construct(ConfigurationService $configurationService)
    {
        $this->configurationService = $configurationService;
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
    }


    public function connect()
    {

        if ($this->rest !== null) {
            return;
        }

        $this->rest = new Rest($this->configurationService->getRestUrl());

        try {
            $token = '';

            if (!empty($this->configurationService->getOAuthClientId()) && !empty($this->configurationService->getOAuthClientSecret())) {
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $this->configurationService->getOAuthTokenUrl());
                curl_setopt($curl, CURLOPT_USERPWD, $this->configurationService->getOAuthClientId() . ":" . $this->configurationService->getOAuthClientSecret());
                curl_setopt($curl, CURLOPT_POSTFIELDS, ["grant_type" => "client_credentials"]);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                $result = curl_exec($curl);
                curl_close ($curl);

                if ($result) {
                    $data = json_decode($result);

                    if (!empty($data->access_token)) {
                        $token = $data->access_token;
                    }
                }
            }

            if (empty($token)) {
                //skip this part if you have an OAuth access token
                $token = $this->rest->post('/login',
                    [
                        'client_id' => $this->configurationService->getClientId(),
                        'login' => $this->configurationService->getLoginName(),
                        'password' => $this->configurationService->getPassword()
                    ]
                );
            }
            $this->rest->setAuthMode('bearer', $token);
        } catch (\Exception $ex) {
            $this->log($ex);
        }

    }


    /**
     * Inserts receiver to a list. Ignores, if already in list.
     *
     * @param mixed $receivers
     * @param int $groupId
     * @return mixed
     */
    public function addReceiversToGroup($receivers, $groupId = null)
    {
        $this->connect();

        if ($groupId === null || $groupId === '') {
            $groupId = $this->configurationService->getGroupId();
        }
        $aReceivers = [];

        if ($receivers instanceof Receiver) {
            $aReceivers[] = $receivers->toArray();
        }
        if (\is_array($receivers)) {
            foreach ((array)$receivers as $receiver) {
                if ($receiver instanceof Receiver) {
                    $aReceivers[] = $receivers->toArray();
                }
            }
        }
        if (\is_string($receivers)) {
            $aReceivers[] = (new Receiver($receivers))->toArray();
        }

        try {
            $return = $this->rest->post('/groups.json/' . $groupId . '/receivers/insert',
                $aReceivers
            );

            if (is_array($return) && !empty($return)) {
                $return = $return[0];
            }

            if (\is_object($return) && $return->status === 'insert success') {
                return true;
            }
        } catch (\Exception $ex) {
            $this->log($ex);
        }


        return false;
    }


    /**
     * TODO
     *
     * @param mixed $receivers
     * @param int $groupId
     */
    public function removeReceiversFromGroup($receivers, $groupId = null)
    {
        $this->connect();

        if ($groupId === null) {
            $groupId = $this->configurationService->getGroupId();
        }

        try {
            $this->rest->delete('/groups.json/' . $groupId . '/receivers/' . $receivers);
        } catch (\Exception $ex) {
            $this->log($ex);
        }
    }


    /**
     * Sets receiver state to inactive
     *
     * @param mixed $receivers
     * @param int $groupId
     */
    public function disableReceiversInGroup($receivers, $groupId = null)
    {
        $this->connect();

        if ($groupId === null) {
            $groupId = $this->configurationService->getGroupId();
        }

        try {
            $this->rest->put('/groups.json/' . $groupId . '/receivers/' . $receivers . '/setinactive');
        } catch (\Exception $ex) {
            $this->log($ex);
        }
    }


    /**
     * Sets receiver state to inactive
     *
     * @param mixed $receivers
     * @param int $groupId
     */
    public function activateReceiversInGroup($receivers, $groupId = null)
    {
        $this->connect();

        if ($groupId === null) {
            $groupId = $this->configurationService->getGroupId();
        }

        try {
            $this->rest->put('/groups.json/' . $groupId . '/receivers/' . $receivers . '/setactive');
        } catch (\Exception $ex) {
            $this->log($ex);
        }
    }


    /**
     * @param int $groupId
     * @return mixed|null
     */
    public function getGroup($groupId = null)
    {
        $this->connect();

        if ($groupId === null || $groupId === '') {
            $groupId = $this->configurationService->getGroupId();
        }

        try {
            return $this->rest->get('/groups.json/' . $groupId);
        } catch (\Exception $ex) {
            $this->log($ex);
        }

        return null;
    }

    /**
     * @param mixed $id id or email
     * @param int $groupId
     * @return bool
     */
    public function isReceiverOfGroup($id, $groupId = null): bool
    {
        $this->connect();

        if ($groupId === null) {
            $groupId = $this->configurationService->getGroupId();
        }

        try {
            $this->rest->get('/groups.json/' . $groupId . '/receivers/' . urlencode($id));
            return true;
        } catch (\Exception $ex) {
            if ($ex->getCode() !== 404) {
                $this->log($ex);
            }
        }
        return false;
    }


    /**
     * @param mixed $id id or email
     * @param int $groupId
     * @return Receiver
     */
    public function getReceiverOfGroup($id, $groupId = null): ?Receiver
    {
        $this->connect();

        if ($groupId === null) {
            $groupId = $this->configurationService->getGroupId();
        }

        try {
            $return = $this->rest->get('/groups.json/' . $groupId . '/receivers/' . urlencode($id));
            return Receiver::createInstance($return);
        } catch (\Exception $ex) {
            if ($ex->getCode() !== 404) {
                $this->log($ex);
            }
        }
        return null;
    }


    /**
     * @param mixed $id id or email
     * @param int $groupId
     * @return bool
     */
    public function isReceiverOfGroupAndActive($id, $groupId = null): bool
    {
        $receiver = $this->getReceiverOfGroup($id, $groupId);
        if ($receiver !== null) {
            return $receiver->isActive();
        }
        return false;
    }


    /**
     * @param string $email
     * @param int $formId
     * @param int $groupId
     */
    public function sendSubscribeMail($email, $formId = null, $groupId = null): void
    {
        $this->connect();

        if ($groupId === null || $groupId === '') {
            $groupId = $this->configurationService->getGroupId();
        }
        if ($formId === null || $formId === '') {
            $formId = $this->configurationService->getFormId();
        }

        $doidata = [
            'user_ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'referer' => $_SERVER['HTTP_REFERER'],
        ];

        try {
            $this->rest->post('/forms.json/' . $formId . '/send/activate',
                [
                    'email' => $email,
                    'groups_id' => $groupId,
                    'doidata' => $doidata,
                ]
            );

        } catch (\Exception $ex) {

            if ($ex->getCode() === 404) {
                // CleverReach sends 404

            }

            $this->log($ex);
        }


    }


    /**
     * @param string $email
     * @param int $formId
     * @param int $groupId
     */
    public function sendUnsubscribeMail($email, $formId = null, $groupId = null): void
    {
        $this->connect();


        if ($groupId === null) {
            $groupId = $this->configurationService->getGroupId();
        }
        if ($formId === null) {
            $formId = $this->configurationService->getFormId();
        }

        $doidata = [
            'user_ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'referer' => $_SERVER['HTTP_REFERER'],
        ];

        try {
            $this->rest->post('/forms.json/' . $formId . '/send/deactivate',
                [
                    'email' => $email,
                    'groups_id' => $groupId,
                    'doidata' => $doidata,
                ]
            );
        } catch (\Exception $ex) {
            $this->log($ex);
        }


    }


    private function log(\Exception $ex): void
    {

        $this->logger->info($ex->getMessage());

    }


    public function setAttributeOfReceiver($email, $attributeId, $value): void
    {
        $this->connect();
        try {
            $this->rest->put('/receivers.json/' . $email . '/attributes/' . $attributeId,
                [
                    'value' => $value,
                ]
            );
        } catch (\Exception $ex) {
            $this->log($ex);
        }

    }


    public function deleteReceiver($email, $groupId = null): void
    {
        $this->connect();
        try {
            $this->rest->delete('/receivers.json/' . $email . '',
                [
                    'group_id' => $groupId,
                ]
            );
        } catch (\Exception $ex) {
            $this->log($ex);
        }

    }


}
