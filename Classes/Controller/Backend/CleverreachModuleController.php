<?php


declare(strict_types=1);

namespace WapplerSystems\Cleverreach\Controller\Backend;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Http\RequestFactory;

#[AsController]
final readonly class CleverreachModuleController
{
    private const AUTHORIZE_URL = 'https://rest.cleverreach.com/oauth/authorize.php';
    private const WHOAMI_URL = 'https://rest.cleverreach.com/v3/debug/whoami.json';

    public function __construct(
        private ModuleTemplateFactory  $moduleTemplateFactory,
        private ExtensionConfiguration $extensionConfiguration,
        private UriBuilder             $backendUriBuilder,
        private RequestFactory         $requestFactory,
    )
    {
    }

    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        $settings = $this->extensionConfiguration->get('cleverreach') ?? [];
        $clientId = $settings['clientId'] ?? '';
        $accessToken = $settings['accessToken'] ?? '';

        $moduleTemplate = $this->moduleTemplateFactory->create($request);

        if ($clientId === '') {
            $moduleTemplate->assignMultiple([
                'isConnected' => false,
                'message' => 'Bitte CleverReach Client-ID in den Extension-Einstellungen (cleverreach) hinterlegen.',
            ]);

            return $moduleTemplate->renderResponse('Module/Cleverreach');
        }

        $uri = $request->getUri();
        $callbackUrl = (string)$uri
            ->withPath('/typo3/cleverreach/oauth/callback')
            ->withQuery('')
            ->withFragment('');

        // URL für CleverReach authorize.php aufbauen
        $authorizeUrl = self::AUTHORIZE_URL . '?' . http_build_query([
                'client_id' => $clientId,
                'grant' => 'basic',
                'response_type' => 'code',
                'redirect_uri' => $callbackUrl,
            ]);

        // Kein Token vorhanden -> direkt zur CleverReach-Loginseite
        if ($accessToken === '') {
            $moduleTemplate = $this->moduleTemplateFactory->create($request);
            $moduleTemplate->assignMultiple([
                'authorizeUrl' => $authorizeUrl,
            ]);
            return $moduleTemplate->renderResponse('Module/CleverreachRedirect');
        }

        // Token prüfen
        $isValid = false;
        $accountInfo = null;

        try {
            $response = $this->requestFactory->request(
                self::WHOAMI_URL,
                'GET',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Accept' => 'application/json',
                    ],
                    'allow_redirects' => false,
                    'timeout' => 5,
                ]
            );

            if ($response->getStatusCode() === 200) {
                $body = $response->getBody()->getContents();
                $decoded = json_decode($body, true);
                if (is_array($decoded)) {
                    $accountInfo = $decoded;
                    $isValid = true;
                }
            }
        } catch (\Throwable $e) {
            $isValid = false;
        }

        if (!$isValid) {
            $moduleTemplate = $this->moduleTemplateFactory->create($request);
            $moduleTemplate->assignMultiple([
                'authorizeUrl' => $authorizeUrl,
            ]);
            return $moduleTemplate->renderResponse('Module/CleverreachRedirect');
        }

        // Token OK -> Status im Modul anzeigen
        $moduleTemplate->assignMultiple([
            'isConnected' => true,
            'accountInfo' => $accountInfo,
            'accessToken' => $accessToken,
        ]);

        return $moduleTemplate->renderResponse('Module/Cleverreach');
    }
}
