<?php
declare(strict_types=1);

namespace WapplerSystems\Cleverreach\Controller\Backend;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use WapplerSystems\Cleverreach\CleverReach\Api;
use WapplerSystems\OauthService\Service\OAuthClientService;

final class FormEditorAjaxController
{
    public function __construct(
        private readonly OAuthClientService $oAuthClientService,
        private readonly Api $api,
    ) {}

    public function getClientsAction(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse($this->oAuthClientService->getActiveClientsAsOptions('cleverreach'));
    }

    /**
     * Returns the groups (Empfängerlisten) the given OAuth client has access to.
     */
    public function getGroupsAction(ServerRequestInterface $request): ResponseInterface
    {
        $empty = [['value' => '', 'label' => '---']];
        $clientUid = (int)($request->getQueryParams()['clientUid'] ?? 0);
        if ($clientUid <= 0) {
            return new JsonResponse($empty);
        }

        if (!$this->authenticateApi($clientUid)) {
            return new JsonResponse($empty);
        }

        try {
            $groups = $this->api->getAllGroups();
        } catch (\Throwable $e) {
            return new JsonResponse([
                ['value' => '', 'label' => '(CleverReach API: ' . $e->getMessage() . ')'],
            ]);
        }

        $options = $empty;
        foreach ($groups as $group) {
            $options[] = [
                'value' => (string)$group['id'],
                'label' => $group['name'] !== '' ? $group['name'] : (string)$group['id'],
            ];
        }
        return new JsonResponse($options);
    }

    /**
     * Returns the forms (Anmeldeformulare) of the given group.
     */
    public function getFormsAction(ServerRequestInterface $request): ResponseInterface
    {
        $query = $request->getQueryParams();
        $clientUid = (int)($query['clientUid'] ?? 0);
        $groupId = (int)($query['groupId'] ?? 0);

        $empty = [['value' => '', 'label' => '---']];
        if ($clientUid <= 0 || $groupId <= 0) {
            return new JsonResponse($empty);
        }

        if (!$this->authenticateApi($clientUid)) {
            return new JsonResponse($empty);
        }

        try {
            $forms = $this->api->getFormsOfGroup($groupId);
        } catch (\Throwable $e) {
            return new JsonResponse([
                ['value' => '', 'label' => '(CleverReach API: ' . $e->getMessage() . ')'],
            ]);
        }

        $options = $empty;
        foreach ($forms as $form) {
            $options[] = [
                'value' => (string)$form['id'],
                'label' => $form['name'] !== '' ? $form['name'] : (string)$form['id'],
            ];
        }
        return new JsonResponse($options);
    }

    private function authenticateApi(int $clientUid): bool
    {
        $connection = $this->oAuthClientService->getActiveConnectionByClientUid($clientUid);
        if ($connection === null || ($connection['access_token'] ?? '') === '') {
            return false;
        }
        $this->api->connectWithToken((string)$connection['access_token']);
        return true;
    }
}