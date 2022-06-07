<?php
namespace WapplerSystems\Cleverreach\Domain\Model;

/**
 * This file is part of the "cleverreach" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */


class Order
{

    /**
     * @var string
     */
    protected $id = '';

    /**
     * @var string
     */
    protected $productId = '';

    /**
     * @var string
     */
    protected $product = '';

    /**
     * @var int
     */
    protected $price = 0;

    /**
     * @var string
     */
    protected $currency = '';

    /**
     * @var int
     */
    protected $amount = 1;

    /**
     * @var int
     */
    protected $mailingId = 0;

    /**
     * @return string
     */
    public function getId(): string
    {
        //@extensionScannerIgnoreLine
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getProductId(): string
    {
        return $this->productId;
    }

    /**
     * @param string $productId
     */
    public function setProductId(string $productId)
    {
        $this->productId = $productId;
    }

    /**
     * @return string
     */
    public function getProduct(): string
    {
        return $this->product;
    }

    /**
     * @param string $product
     */
    public function setProduct(string $product)
    {
        $this->product = $product;
    }

    /**
     * @return int
     */
    public function getPrice(): int
    {
        return $this->price;
    }

    /**
     * @param int $price
     */
    public function setPrice(int $price)
    {
        $this->price = $price;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency(string $currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount(int $amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return int
     */
    public function getMailingId(): int
    {
        return $this->mailingId;
    }

    /**
     * @param int $mailingId
     */
    public function setMailingId(int $mailingId)
    {
        $this->mailingId = $mailingId;
    }




}
