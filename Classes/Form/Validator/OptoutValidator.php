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
class OptoutValidator extends AbstractValidator
{


    /**
     * @var \WapplerSystems\Cleverreach\CleverReach\Api
     */
    protected $api;


    /**
     * Checks if the given value is already in the list
     *
     * @param mixed $value The value that should be validated
     * @api
     */
    public function isValid($value)
    {

        /** @var ConfigurationService $configurationService */
        $configurationService = GeneralUtility::makeInstance(ConfigurationService::class);
        $configuration = $configurationService->getConfiguration();

        $groupId = ($this->options['groupId'] ?? '') !== '' ? $this->options['groupId'] : $configuration['groupId'];

        if (empty($groupId)) {
            $this->addError('Group ID not set.', 1534719428);
            return;
        }

        if (!$this->api->isReceiverOfGroupAndActive($value, $groupId)) {
            $this->addError(
                $this->translateErrorMessage(
                    'validator.notInList',
                    'cleverreach'
                ),
                1534719523
            );
        }
    }

}
