<?php
namespace WapplerSystems\Cleverreach\Form\Validator;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;
use WapplerSystems\Cleverreach\Service\ConfigurationService;

/**
 * Validator for email addresses
 *
 * @api
 */
class OptinValidator extends AbstractValidator
{

    /**
     * @var \WapplerSystems\Cleverreach\CleverReach\Api
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $api;

    /**
     * @var array
     */
    protected $supportedOptions = [
        'groupId' => ['', 'The groupId', 'string', false]
    ];

    /**
     * Checks if the given value is already in the list
     *
     * @param mixed $value The value that should be validated
     * @api
     */
    public function isValid($value)
    {

        /** @var ConfigurationService $configurationService */
        $configurationService = GeneralUtility::makeInstance(ObjectManager::class)->get(ConfigurationService::class);
        $configuration = $configurationService->getConfiguration();

        $groupId = isset($this->options['groupId']) && \strlen($this->options['groupId']) > 0 ? $this->options['groupId'] : $configuration['groupId'];

        if (empty($groupId)) {
            $this->addError('Group ID not set.',1534719428);
            return;
        }

        if ($this->api->isReceiverOfGroupAndActive($value,$groupId)) {
            $this->addError(
                $this->translateErrorMessage(
                    'validator.alreadyInList',
                    'cleverreach'
                ),
                1534719423
            );
        }
    }

}
