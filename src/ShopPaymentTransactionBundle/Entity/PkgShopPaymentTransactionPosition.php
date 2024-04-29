<?php

namespace ChameleonSystem\ShopPaymentTransactionBundle\Entity;

use ChameleonSystem\ShopBundle\Entity\ShopOrder\ShopOrderItem;

class PkgShopPaymentTransactionPosition
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var PkgShopPaymentTransaction|null - Belongs to transaction */
        private ?PkgShopPaymentTransaction $pkgShopPaymentTransaction = null
        ,
        // TCMSFieldNumber
        /** @var int - Amount */
        private int $amount = 0,
        // TCMSFieldDecimal
        /** @var string - Value */
        private string $value = '',
        // TCMSFieldOption
        /** @var string - Type */
        private string $type = 'product',
        // TCMSFieldExtendedLookup
        /** @var ShopOrderItem|null - Order item */
        private ?ShopOrderItem $shopOrderItem = null
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getCmsident(): ?int
    {
        return $this->cmsident;
    }

    public function setCmsident(int $cmsident): self
    {
        $this->cmsident = $cmsident;

        return $this;
    }

    // TCMSFieldLookupParentID
    public function getPkgShopPaymentTransaction(): ?PkgShopPaymentTransaction
    {
        return $this->pkgShopPaymentTransaction;
    }

    public function setPkgShopPaymentTransaction(?PkgShopPaymentTransaction $pkgShopPaymentTransaction): self
    {
        $this->pkgShopPaymentTransaction = $pkgShopPaymentTransaction;

        return $this;
    }


    // TCMSFieldNumber
    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }


    // TCMSFieldDecimal
    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }


    // TCMSFieldOption
    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }


    // TCMSFieldExtendedLookup
    public function getShopOrderItem(): ?ShopOrderItem
    {
        return $this->shopOrderItem;
    }

    public function setShopOrderItem(?ShopOrderItem $shopOrderItem): self
    {
        $this->shopOrderItem = $shopOrderItem;

        return $this;
    }


}
