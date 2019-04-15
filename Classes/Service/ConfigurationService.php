<?php
namespace WapplerSystems\Cleverreach\Service;

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
class ConfigurationService {


    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $objectManager;


    public function getConfiguration() {

        /** @var ConfigurationManagerInterface $configurationManager */
        $configurationManager = $this->objectManager->get(ConfigurationManagerInterface::class);
        $settings = $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT,
            'cleverreach'
        );
        return $settings['plugin.']['tx_cleverreach.']['settings.'];
    }

    /**
     * @return string
     */
    public function getRestUrl() {

        $config = $this->getConfiguration();
        return $config['restUrl'];

    }

    /**
     * @return string
     */
    public function getClientId() {

        $config = $this->getConfiguration();
        return $config['clientId'];

    }

    /**
     * @return string
     */
    public function getLoginName() {
        $config = $this->getConfiguration();
        return $config['login'];
    }

    /**
     * @return string
     */
    public function getPassword() {
        $config = $this->getConfiguration();
        return $config['password'];
    }

    /**
     * @return int
     */
    public function getGroupId() {
        $config = $this->getConfiguration();
        return (int)$config['groupId'];
    }

    /**
     * @return int
     */
    public function getFormId() {
        $config = $this->getConfiguration();
        return (int)$config['formId'];
    }

    /**
     * @return string
     */
    public function getUnsubscribeMethod() {
        $config = $this->getConfiguration();
        return $config['unsubscribemethod'];
    }

}
