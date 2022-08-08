<?php
namespace WapplerSystems\Cleverreach\Powermail\Validator;

/**
 * This file is part of the "cleverreach" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */


use TYPO3\CMS\Core\Utility\GeneralUtility;
use WapplerSystems\Cleverreach\CleverReach\Api;

class OptinValidator
{


    /**
     * Check if given number is higher than in configuration
     *
     * @param string $value
     * @param string $validationConfiguration
     * @return bool
     */
    public function validate120($value, $validationConfiguration): bool
    {
        $value = trim($value);

        if (!GeneralUtility::validEmail($value)) {
            return FALSE;
        }
        $api = GeneralUtility::makeInstance(Api::class);
        return !$api->isReceiverOfGroupAndActive($value);
    }


}
