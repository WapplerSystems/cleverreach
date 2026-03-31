<?php

namespace WapplerSystems\Cleverreach\CleverReach;

/**
 * This file is part of the "cleverreach" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */


use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WapplerSystems\Cleverreach\Domain\Model\Receiver;
use WapplerSystems\Cleverreach\Tools\Rest;


class Api
{


    protected ?Rest $rest;

    protected Logger $logger;

    public const string MODE_OPTIN = 'optin';

    public const string MODE_OPTOUT = 'optout';

    public function __construct()
    {
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        $this->rest = new Rest('https://rest.cleverreach.com/v3');
    }

    public function connectWithToken(string $accessToken): void
    {
        $this->rest->setToken($accessToken);
    }


    /**
     * Inserts receiver to a list. Ignores, if already in list.
     *
     */
    public function addReceiversToList($receivers, ?int $listId = null): bool
    {

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
            $return = $this->rest->post('/groups.json/' . $listId . '/receivers/insert',
                $aReceivers
            );

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
     */
    public function removeReceiversFromGroup($receivers, ?int $listId = null): void
    {

        try {
            $this->rest->delete('/groups.json/' . $listId . '/receivers/' . $receivers);
        } catch (\Exception $ex) {
            $this->log($ex);
        }
    }


    /**
     * Sets receiver state to inactive
     *
     */
    public function disableReceiversInGroup($receivers, ?int $listId = null): void
    {

        try {
            $this->rest->put('/groups.json/' . $listId . '/receivers/' . $receivers . '/setinactive');
        } catch (\Exception $ex) {
            $this->log($ex);
        }
    }


    /**
     * Sets receiver state to inactive
     *
     * @param mixed $receivers
     * @param int $listId
     */
    public function activateReceiversInGroup($receivers, $listId = null)
    {
        try {
            $this->rest->put('/groups.json/' . $listId . '/receivers/' . $receivers . '/setactive');
        } catch (\Exception $ex) {
            $this->log($ex);
        }
    }


    public function getList(int $listId)
    {
        try {
            return $this->rest->get('/groups.json/' . $listId);
        } catch (\Exception $ex) {
            $this->log($ex);
        }

        return null;
    }

    public function isReceiverOfGroup(int $id, int $listId): bool
    {
        try {
            $this->rest->get('/groups.json/' . $listId . '/receivers/' . $id);

            return true;
        } catch (\Exception $ex) {
            if ($ex->getCode() !== 404) {
                $this->log($ex);
            }
        }
        return false;
    }


    public function getReceiverOfGroup(int $id, int $listId): ?Receiver
    {
        try {
            $return = $this->rest->get('/groups.json/' . $listId . '/receivers/' . $id);

            return Receiver::createInstance($return);
        } catch (\Exception $ex) {
            if ($ex->getCode() !== 404) {
                $this->log($ex);
            }
        }
        return null;
    }


    public function isReceiverOfGroupAndActive(int $id, int $listId): bool
    {
        $receiver = $this->getReceiverOfGroup($id, $listId);
        if ($receiver !== null) {
            return $receiver->isActive();
        }
        return false;
    }


    public function sendSubscribeMail(string $email, int $formId, int $listId): void
    {
        $doidata = [
            'user_ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'referer' => $_SERVER['HTTP_REFERER'],
        ];

        try {
            $this->rest->post('/forms.json/' . $formId . '/send/activate',
                [
                    'email' => $email,
                    'groups_id' => $listId,
                    'doidata' => $doidata,
                ]
            );

        } catch (\Exception $ex) {

            if ($ex->getCode() === 403) {
                throw new \RuntimeException($ex->getMessage());
            }

            debug($ex);
            if ($ex->getCode() === 404) {
                // CleverReach sends 404

            }

            $this->log($ex);
        }

    }


    /**
     * @param string $email
     * @param int|null $formId
     * @param int $listId
     */
    public function sendUnsubscribeMail(string $email, ?int $formId = null, $listId = null): void
    {
        $doidata = [
            'user_ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'referer' => $_SERVER['HTTP_REFERER'],
        ];

        try {
            $this->rest->post('/forms.json/' . $formId . '/send/deactivate',
                [
                    'email' => $email,
                    'groups_id' => $listId,
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


    public function deleteReceiver($email, $listId = null): void
    {
        try {
            $this->rest->delete('/receivers.json/' . $email,
                [
                    'group_id' => $listId,
                ]
            );
        } catch (\Exception $ex) {
            $this->log($ex);
        }

    }


}
