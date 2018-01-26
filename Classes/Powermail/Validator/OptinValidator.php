<?php
namespace WapplerSystems\Cleverreach\Powermail\Validator;

/**
 * This file is part of the "cleverreach" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */


use TYPO3\CMS\Core\Utility\GeneralUtility;

class OptinValidator
{

    /**
     * @var \WapplerSystems\Cleverreach\CleverReach\Api
     * @inject
     */
    protected $api;

    /**
     * Check if given number is higher than in configuration
     *
     * @param string $value
     * @param string $validationConfiguration
     * @return bool
     */
    public function validateOptin($value, $validationConfiguration): bool
    {
        $value = trim($value);

        if (!GeneralUtility::validEmail($value)) {
            return FALSE;
        }
        return !$this->api->isReceiverOfGroupAndActive($value);
    }


}
