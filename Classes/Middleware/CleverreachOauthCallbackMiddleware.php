<?php


declare(strict_types=1);

namespace WapplerSystems\Cleverreach\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Http\RequestFactory;

final class CleverreachOauthCallbackMiddleware implements MiddlewareInterface
{
    private const TOKEN_URL = 'https://rest.cleverreach.com/oauth/token.php';

    public function __construct(
        private readonly ExtensionConfiguration $extensionConfiguration,
        private readonly RequestFactory         $requestFactory,
    )
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();

        // Nur unsere Callback-URL abfangen, alles andere normal weiterreichen
        if ($path !== '/typo3/cleverreach/oauth/callback') {
            return $handler->handle($request);
        }


        $queryParams = $request->getQueryParams();
        $code = $queryParams['code'] ?? null;

        if (empty($code)) {
            return new HtmlResponse('Missing "code" parameter.', 400);
        }

        $settings = $this->extensionConfiguration->get('cleverreach') ?? [];
        $clientId = $settings['clientId'] ?? '';
        $clientSecret = $settings['clientSecret'] ?? '';

        if ($clientId === '' || $clientSecret === '') {
            return new HtmlResponse('Missing clientId/clientSecret in EXT:cleverreach settings.', 500);
        }

        // redirect_uri muss exakt der Callback-URL entsprechen (ohne Query)
        $callbackUri = $request->getUri()->withQuery('');
        $callbackUrl = (string)$callbackUri;

        // Code gegen Token tauschen
        try {
            $response = $this->requestFactory->request(
                self::TOKEN_URL,
                'POST',
                [
                    'form_params' => [
                        'client_id' => $clientId,
                        'client_secret' => $clientSecret,
                        'grant_type' => 'authorization_code',
                        'code' => $code,
                        'redirect_uri' => $callbackUrl,
                    ],
                    'timeout' => 5,
                ]
            );
        } catch (\Throwable $e) {
            return new HtmlResponse('Error requesting token: ' . $e->getMessage(), 500);
        }

        if ($response->getStatusCode() !== 200) {
            return new HtmlResponse(
                'Token endpoint returned status ' . $response->getStatusCode(),
                500
            );
        }

        $data = json_decode($response->getBody()->getContents(), true);
        if (!is_array($data) || empty($data['access_token'])) {
            return new HtmlResponse('No "access_token" in response.', 500);
        }

        // Token in Extension-Settings speichern (komplett ohne BE-Session)
        $settings['accessToken'] = $data['access_token'];

        if (!empty($data['refresh_token'])) {
            $settings['refreshToken'] = $data['refresh_token'];
        }

        if (!empty($data['expires_in'])) {
            $settings['tokenExpiresAt'] = time() + (int)$data['expires_in'];
        }

        $this->extensionConfiguration->set('cleverreach', $settings);

        $backendEntryPoint = $GLOBALS['TYPO3_CONF_VARS']['BE']['entryPoint'] ?? '/typo3';

        $uri = $request->getUri();

        $backendModuleUri = $uri
            ->withPath($backendEntryPoint . '/module/system/cleverreach')
            ->withQuery('')
            ->withFragment('');

        $backendModuleUrl = (string)$backendModuleUri;

// Kleine HTML-Seite mit Hinweis + Link zurück ins Backend
        $html = '<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>CleverReach verbunden</title>
    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            padding: 2rem;
            max-width: 600px;
            margin: 0 auto;
        }
        .box {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 1.5rem;
        }
        a.button {
            display: inline-block;
            padding: 0.6rem 1.2rem;
            border-radius: 4px;
            text-decoration: none;
            border: 1px solid #005262;
            color: #fff;
            background: #005262;
            margin-top: 1rem;
        }
        a.button:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="box">
        <h1>CleverReach erfolgreich verbunden</h1>
        <p>Der Zugriffstoken wurde gespeichert.</p>
        <p>Klicke auf den folgenden Link, um zum TYPO3 Backend-Modul zurückzukehren:</p>
        <p>
            <a class="button" href="' . htmlspecialchars($backendModuleUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" target="_top">
                Zum CleverReach Backend-Modul
            </a>
        </p>
        <p style="margin-top:1rem;font-size:0.9rem;color:#666;">
            Hinweis: Der Link öffnet das TYPO3 Backend im Hauptfenster.
            Falls du noch nicht angemeldet bist, erscheint zunächst der Login.
        </p>
    </div>
</body>
</html>';

        return new HtmlResponse($html);
    }
}
