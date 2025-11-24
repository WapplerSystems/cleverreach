<?php

namespace WapplerSystems\Cleverreach\Service;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * This file is part of the "cleverreach" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */


/**
 * Class ConfigurationService
 */
class ConfigurationService
{

    public function __construct(
        private readonly ExtensionConfiguration $extensionConfiguration,
    )
    {

    }

    public function getConfiguration(): array
    {

        $configurationManager = GeneralUtility::makeInstance(ConfigurationManagerInterface::class);
        $settings = $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT,
            'cleverreach'
        );

        return $settings['plugin.']['tx_cleverreach.']['settings.'];
    }

    /**
     * @return int
     */
    public function getListId(): int
    {
        $config = $this->getConfiguration();
        return (int)$config['listId'];
    }

    /**
     * @return int
     */
    public function getFormId(): int
    {
        $config = $this->getConfiguration();
        return (int)$config['formId'];
    }

    /**
     * @return string
     */
    public function getUnsubscribeMethod(): string
    {
        $config = $this->getConfiguration();
        return $config['unsubscribemethod'];
    }


    public function getClientId()
    {
        $settings = $this->extensionConfiguration->get('cleverreach') ?? [];
        return $settings['clientId'] ?? '';
    }

    public function getAccessToken()
    {
        $settings = $this->extensionConfiguration->get('cleverreach') ?? [];
        return $settings['accessToken'] ?? '';
    }

    public function getRefreshToken()
    {
        $settings = $this->extensionConfiguration->get('cleverreach') ?? [];
        return $settings['refreshToken'] ?? '';
    }

    public function getTokenExpiresAt()
    {
        $settings = $this->extensionConfiguration->get('cleverreach') ?? [];
        return $settings['tokenExpiresAt'] ?? '';
    }


}
