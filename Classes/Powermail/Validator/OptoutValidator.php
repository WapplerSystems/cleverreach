<?php
namespace WapplerSystems\Cleverreach\Powermail\Validator;

/**
 * This file is part of the "cleverreach" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */


class OptoutValidator
{

    /**
     * @var \WapplerSystems\Cleverreach\CleverReach\Api
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $api;

    /**
     * Check if given number is higher than in configuration
     *
     * @param string $value
     * @param string $validationConfiguration
     * @return bool
     */
    public function validate121($value, $validationConfiguration): bool
    {
        $value = trim($value);

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return FALSE;
        }
        return $this->api->isReceiverOfGroupAndActive($value);
    }


}
