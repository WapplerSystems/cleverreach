<?php
namespace WapplerSystems\Cleverreach\Domain\Model;

/**
 * This file is part of the "cleverreach" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */


class Receiver
{

    /**
     * @var string
     */
    protected $email = '';

    /**
     * timestamp
     * @var int
     */
    protected $activated = 0;

    /**
     * timestamp
     * @var int
     */
    protected $registered = 0;

    /**
     * timestamp
     * @var int
     */
    protected $deactivated = 0;

    /**
     * @var string
     */
    protected $source = '';

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var array
     */
    protected $globalAttributes = [];

    /**
     * @var array
     */
    protected $orders = [];


    /**
     * Receiver constructor.
     * @param string $email
     * @param array $attributes
     */
    public function __construct($email,$attributes = null) {
        $this->email = $email;
        $this->attributes = $attributes;
        $this->registered = time();
    }


    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'activated' => $this->activated,
            'deactivated' => $this->deactivated,
            'registered' => $this->registered,
            'attributes' => $this->attributes,
            'global_attributes' => $this->attributes,
            'source' => 'TYPO3',
        ];
    }


    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    /**
     * @return int
     */
    public function getActivated(): int
    {
        return $this->activated;
    }

    /**
     * @param int $activated
     */
    public function setActivated(int $activated)
    {
        $this->activated = $activated;
    }

    /**
     * @return int
     */
    public function getRegistered(): int
    {
        return $this->registered;
    }

    /**
     * @param int $registered
     */
    public function setRegistered(int $registered)
    {
        $this->registered = $registered;
    }

    /**
     * @return int
     */
    public function getDeactivated(): int
    {
        return $this->deactivated;
    }

    /**
     * @param int $deactivated
     */
    public function setDeactivated(int $deactivated)
    {
        $this->deactivated = $deactivated;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @param string $source
     */
    public function setSource(string $source)
    {
        $this->source = $source;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return array
     */
    public function getGlobalAttributes(): array
    {
        return $this->globalAttributes;
    }

    /**
     * @param array $globalAttributes
     */
    public function setGlobalAttributes(array $globalAttributes)
    {
        $this->globalAttributes = $globalAttributes;
    }

    /**
     * @return array
     */
    public function getOrders(): array
    {
        return $this->orders;
    }

    /**
     * @param array $orders
     */
    public function setOrders(array $orders)
    {
        $this->orders = $orders;
    }


}