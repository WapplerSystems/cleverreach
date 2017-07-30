<?php
declare(strict_types=1);
namespace WapplerSystems\Cleverreach\Form\Finishers;


/**
 * This file is part of the "cleverreach" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */


use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;
use TYPO3\CMS\Form\Domain\Finishers\Exception\FinisherException;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use WapplerSystems\Cleverreach\Domain\Model\Receiver;


class CleverreachFinisher extends AbstractFinisher
{

    /**
     * @var \WapplerSystems\Cleverreach\CleverReach\Api
     * @inject
     */
    protected $api;


    /**
     * @var array
     */
    protected $defaultOptions = [
    ];


    /**
     * Executes this finisher
     * @see AbstractFinisher::execute()
     *
     * @throws FinisherException
     */
    protected function executeInternal()
    {

        $formValues = $this->getFormValues();

        $email = null;
        $attributes = [];


        foreach ($formValues as $identifier => $value) {

            $element = $this->finisherContext->getFormRuntime()->getFormDefinition()->getElementByIdentifier($identifier);
            $properties = $element->getProperties();
            if (isset($properties['cleverreachField'])) {
                if ($properties['cleverreachField'] === 'email') {
                    $email = $value;
                } else {
                    $attributes[$properties['cleverreachField']] = $value;
                }

            }

        }

        if (isset($this->options['mode']) && strlen($email) > 0) {

            if ($this->options['mode'] === 'Optin') {

                $receiver = new Receiver($email,$attributes);
                $this->api->addReceiversToGroup($receiver,$this->options['groupId']);
                $this->api->sendSubscribeMail($email,$this->options['formId'],$this->options['groupId']);

            } else if ($this->options['mode'] === 'Optout') {

                $this->api->sendUnsubscribeMail($email);

            }

        }


    }




    /**
     * Returns the values of the submitted form
     *
     * @return []
     */
    protected function getFormValues(): array
    {
        return $this->finisherContext->getFormValues();
    }

    /**
     * Returns a form element object for a given identifier.
     *
     * @param string $elementIdentifier
     * @return NULL|FormElementInterface
     */
    protected function getElementByIdentifier(string $elementIdentifier)
    {
        return $this
            ->finisherContext
            ->getFormRuntime()
            ->getFormDefinition()
            ->getElementByIdentifier($elementIdentifier);
    }
}
