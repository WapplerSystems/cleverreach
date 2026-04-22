<?php
declare(strict_types=1);

namespace WapplerSystems\Cleverreach\Controller\Backend;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use WapplerSystems\OauthService\Service\OAuthClientService;

final class FormEditorAjaxController
{
    public function __construct(
        private readonly OAuthClientService $oAuthClientService,
    ) {}

    public function getClientsAction(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse($this->oAuthClientService->getActiveClientsAsOptions('cleverreach'));
    }
}
