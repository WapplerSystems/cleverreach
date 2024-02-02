<?php

namespace WapplerSystems\Cleverreach\Service;

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

    public function getConfiguration(): string
    {

        $configurationManager = GeneralUtility::makeInstance(ConfigurationManagerInterface::class);
        $settings = $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT,
            'cleverreach'
        );

        return $settings['plugin.']['tx_cleverreach.']['settings.'];
    }

    /**
     * @return string
     */
    public function getRestUrl(): string
    {

        $config = $this->getConfiguration();

        return $config['restUrl'];
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {

        $config = $this->getConfiguration();

        return $config['clientId'];

    }

    /**
     * @return string
     */
    public function getLoginName(): string
    {
        $config = $this->getConfiguration();

        return $config['login'];
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        $config = $this->getConfiguration();

        return $config['password'];
    }

    /**
     * @return int
     */
    public function getGroupId(): string
    {
        $config = $this->getConfiguration();

        return (int)$config['groupId'];
    }

    /**
     * @return int
     */
    public function getFormId(): string
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

    /**
     * @return string
     */
    public function getAuthMode(): string
    {
        return $this->getConfiguration()['authMode'];
    }

    /**
     * @return string
     */
    public function getOauthTokenUrl(): string
    {
        return $this->getConfiguration()['oauthTokenUrl'];
    }

    /**
     * @return string
     */
    public function getOauthClientId(): string
    {
        return $this->getConfiguration()['oauthClientId'];
    }

    /**
     * @return string
     */
    public function getOauthClientSecret(): string
    {
        return $this->getConfiguration()['oauthClientSecret'];
    }

    /**
     * @return string
     */
    public function getOauthClientCode(): string
    {
        return $this->getConfiguration()['oauthClientCode'];
    }

}
