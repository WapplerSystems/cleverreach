<?php
declare(strict_types=1);

namespace WapplerSystems\Cleverreach\Form\Validator;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;
use WapplerSystems\Cleverreach\CleverReach\Api;
use WapplerSystems\Cleverreach\Service\CleverreachFormContext;

class OptoutValidator extends AbstractValidator
{
    public function isValid(mixed $value): void
    {
        $context = GeneralUtility::makeInstance(CleverreachFormContext::class);

        $groupId = $context->get('groupId');
        if (empty($groupId)) {
            $this->addError('Group ID not set.', 1534719428);
            return;
        }

        $api = GeneralUtility::makeInstance(Api::class);

        if (!$api->isReceiverOfGroupAndActive($value, $groupId)) {
            $this->addError(
                $this->translateErrorMessage('validator.notInList', 'cleverreach'),
                1534719523
            );
        }
    }
}
