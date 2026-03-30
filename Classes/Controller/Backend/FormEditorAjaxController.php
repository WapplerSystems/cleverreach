<?php
declare(strict_types=1);

namespace WapplerSystems\Cleverreach\Controller\Backend;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Service\TranslationService;
use WapplerSystems\OauthService\Domain\Repository\ConnectionRepository;

final class FormEditorAjaxController
{
    public function __construct(
        private readonly ConnectionRepository $connectionRepository,
    ) {}

    public function getConnectionsAction(ServerRequestInterface $request): ResponseInterface
    {
        $connections = $this->connectionRepository->findActiveByProvider('cleverreach');
        $translationService = GeneralUtility::makeInstance(TranslationService::class);

        $options = [['value' => '', 'label' => '---']];
        foreach ($connections as $conn) {
            $options[] = [
                'value' => (string)$conn['uid'],
                'label' => $translationService->translate('LLL:EXT:oauth_service/Resources/Private/Language/locallang_mod.xlf:connection') .' #' . $conn['uid'],
            ];
        }

        return new JsonResponse($options);
    }
}
