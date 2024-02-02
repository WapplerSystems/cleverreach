<?php

namespace WapplerSystems\Cleverreach\CleverReach;

/**
 * This file is part of the "cleverreach" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
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

    private const CACHE_KEY = 'ws_cleverreach_token_storage';

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
            if ($this->configurationService->getAuthMode() === 'oauth') {
                $token = $this->authenticateViaOAuth();
            } else {
                $token = $this->rest->post('/login',
                    [
                        'client_id' => $this->configurationService->getClientId(),
                        'login' => $this->configurationService->getLoginName(),
                        'password' => $this->configurationService->getPassword(),
                    ]
                );
            }
            $this->rest->setAuthMode('bearer', $token);
        } catch (\Exception | GuzzleException $ex) {
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
            $return = $this->rest->post('/groups.json/'.$groupId.'/receivers/insert',
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
            $this->rest->delete('/groups.json/'.$groupId.'/receivers/'.$receivers);
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
            $this->rest->put('/groups.json/'.$groupId.'/receivers/'.$receivers.'/setinactive');
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
            $this->rest->put('/groups.json/'.$groupId.'/receivers/'.$receivers.'/setactive');
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
            return $this->rest->get('/groups.json/'.$groupId);
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
            $this->rest->get('/groups.json/'.$groupId.'/receivers/'.$id);

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
            $return = $this->rest->get('/groups.json/'.$groupId.'/receivers/'.$id);

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
            $this->rest->post('/forms.json/'.$formId.'/send/activate',
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
            $this->rest->post('/forms.json/'.$formId.'/send/deactivate',
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
            $this->rest->put('/receivers.json/'.$email.'/attributes/'.$attributeId,
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
            $this->rest->delete('/receivers.json/'.$email.'',
                [
                    'group_id' => $groupId,
                ]
            );
        } catch (\Exception $ex) {
            $this->log($ex);
        }

    }

    /**
     * @throws NoSuchCacheException
     * @throws GuzzleException
     * @throws \Exception
     */
    protected function authenticateViaOAuth(): string
    {
        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        $cache = $cacheManager->getCache('hash');

        if ($cachedAccessToken = $cache->get(self::CACHE_KEY)) {
            return $cachedAccessToken;
        }

        $uri = new Uri($this->configurationService->getOauthTokenUrl());

        $arguments = [
            'grant_type'    => 'client_credentials',
            'client_id'     => $this->configurationService->getOauthClientId(),
            'client_secret' => $this->configurationService->getOAuthClientSecret(),
        ];

        $request = new Request(
            'POST',
            $uri,
            [
                'Content-Type'  => 'application/json; charset=utf-8',
            ],
            \GuzzleHttp\json_encode($arguments)
        );

        $client = new Client();
        $rawResponse = $client->send($request, ['http_errors' => false]);
        $responseData = json_decode($rawResponse->getBody()->getContents(), true);

        if (200 !== $rawResponse->getStatusCode()) {
            $errorMessage = $responseData['error']['message'] ?? 'Error message not specified';

            throw new \Exception(sprintf('CleverReach Api Error: %s', $errorMessage), 1706886072);
        }

        if (empty($responseData['access_token'])) {
            throw new \Exception('CleverReach oAuth failed: %s', 1706886133);
        }

        // cache result for expire time minus 2 minutes buffer
        $cache->set(self::CACHE_KEY, $responseData['access_token'], [], ($responseData['expires_in']) - 120);

        return $responseData['access_token'];
    }

}
