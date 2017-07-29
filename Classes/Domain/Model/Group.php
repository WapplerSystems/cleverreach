<?php
namespace WapplerSystems\Cleverreach\Domain\Model;

/**
 * This file is part of the "cleverreach" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */


class Group
{


    protected $id;

    protected $name;

    protected $lastMailing = 0;

    protected $lastChanged = 0;

    protected $stamp = 0;



    /**
     * @var bool
     */
    protected $locked = false;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getLastMailing(): int
    {
        return $this->lastMailing;
    }

    /**
     * @param int $lastMailing
     */
    public function setLastMailing(int $lastMailing)
    {
        $this->lastMailing = $lastMailing;
    }

    /**
     * @return int
     */
    public function getLastChanged(): int
    {
        return $this->lastChanged;
    }

    /**
     * @param int $lastChanged
     */
    public function setLastChanged(int $lastChanged)
    {
        $this->lastChanged = $lastChanged;
    }

    /**
     * @return bool
     */
    public function isLocked(): bool
    {
        return $this->locked;
    }

    /**
     * @param bool $locked
     */
    public function setLocked(bool $locked)
    {
        $this->locked = $locked;
    }

    /**
     * @return int
     */
    public function getStamp(): int
    {
        return $this->stamp;
    }

    /**
     * @param int $stamp
     */
    public function setStamp(int $stamp)
    {
        $this->stamp = $stamp;
    }




}