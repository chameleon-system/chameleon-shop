<?php

namespace ChameleonSystem\ShopBundle\Entity\ShopVoucher;

use ChameleonSystem\ShopBundle\Entity\ShopCore\PkgShopCurrency;
use ChameleonSystem\ShopBundle\Entity\ShopOrder\ShopOrder;

class ShopVoucherUse
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var ShopVoucher|null - Belongs to voucher */
        private ?ShopVoucher $shopVoucher = null,
        // TCMSFieldDateTime
        /** @var \DateTime|null - Used on */
        private ?\DateTime $dateUsed = null,
        // TCMSFieldDecimal
        /** @var string - Value used up */
        private string $valueUsed = '',
        // TCMSFieldLookupParentID
        /** @var ShopOrder|null - Used in this order */
        private ?ShopOrder $shopOrder = null,
        // TCMSFieldDecimal
        /** @var string - Value consumed in the order currency */
        private string $valueUsedInOrderCurrency = '',
        // TCMSFieldExtendedLookup
        /** @var PkgShopCurrency|null - Currency in which the order was made */
        private ?PkgShopCurrency $pkgShopCurrency = null
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
    public function getShopVoucher(): ?ShopVoucher
    {
        return $this->shopVoucher;
    }

    public function setShopVoucher(?ShopVoucher $shopVoucher): self
    {
        $this->shopVoucher = $shopVoucher;

        return $this;
    }

    // TCMSFieldDateTime
    public function getDateUsed(): ?\DateTime
    {
        return $this->dateUsed;
    }

    public function setDateUsed(?\DateTime $dateUsed): self
    {
        $this->dateUsed = $dateUsed;

        return $this;
    }

    // TCMSFieldDecimal
    public function getValueUsed(): string
    {
        return $this->valueUsed;
    }

    public function setValueUsed(string $valueUsed): self
    {
        $this->valueUsed = $valueUsed;

        return $this;
    }

    // TCMSFieldLookupParentID
    public function getShopOrder(): ?ShopOrder
    {
        return $this->shopOrder;
    }

    public function setShopOrder(?ShopOrder $shopOrder): self
    {
        $this->shopOrder = $shopOrder;

        return $this;
    }

    // TCMSFieldDecimal
    public function getValueUsedInOrderCurrency(): string
    {
        return $this->valueUsedInOrderCurrency;
    }

    public function setValueUsedInOrderCurrency(string $valueUsedInOrderCurrency): self
    {
        $this->valueUsedInOrderCurrency = $valueUsedInOrderCurrency;

        return $this;
    }

    // TCMSFieldExtendedLookup
    public function getPkgShopCurrency(): ?PkgShopCurrency
    {
        return $this->pkgShopCurrency;
    }

    public function setPkgShopCurrency(?PkgShopCurrency $pkgShopCurrency): self
    {
        $this->pkgShopCurrency = $pkgShopCurrency;

        return $this;
    }
}
