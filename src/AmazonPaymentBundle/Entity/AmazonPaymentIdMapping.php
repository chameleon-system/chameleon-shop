<?php

namespace ChameleonSystem\AmazonPaymentBundle\Entity;

use ChameleonSystem\ShopBundle\Entity\ShopOrder\ShopOrder;
use ChameleonSystem\ShopPaymentTransactionBundle\Entity\PkgShopPaymentTransaction;

class AmazonPaymentIdMapping
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var ShopOrder|null - Belongs to order */
        private ?ShopOrder $shopOrder = null
        ,
        // TCMSFieldVarchar
        /** @var string - Amazon order reference ID */
        private string $amazonOrderReferenceId = '',
        // TCMSFieldVarchar
        /** @var string - Local reference ID */
        private string $localId = '',
        // TCMSFieldVarchar
        /** @var string - Amazon ID */
        private string $amazonId = '',
        // TCMSFieldDecimal
        /** @var float - Value */
        private float $value = 0,
        // TCMSFieldNumber
        /** @var int - Type */
        private int $type = 0,
        // TCMSFieldNumber
        /** @var int - Request mode */
        private int $requestMode = 1,
        // TCMSFieldBoolean
        /** @var bool - CaptureNow */
        private bool $captureNow = false,
        // TCMSFieldExtendedLookup
        /** @var PkgShopPaymentTransaction|null - Belongs to transaction */
        private ?PkgShopPaymentTransaction $pkgShopPaymentTransaction = null
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
    public function getShopOrder(): ?ShopOrder
    {
        return $this->shopOrder;
    }

    public function setShopOrder(?ShopOrder $shopOrder): self
    {
        $this->shopOrder = $shopOrder;

        return $this;
    }


    // TCMSFieldVarchar
    public function getAmazonOrderReferenceId(): string
    {
        return $this->amazonOrderReferenceId;
    }

    public function setAmazonOrderReferenceId(string $amazonOrderReferenceId): self
    {
        $this->amazonOrderReferenceId = $amazonOrderReferenceId;

        return $this;
    }


    // TCMSFieldVarchar
    public function getLocalId(): string
    {
        return $this->localId;
    }

    public function setLocalId(string $localId): self
    {
        $this->localId = $localId;

        return $this;
    }


    // TCMSFieldVarchar
    public function getAmazonId(): string
    {
        return $this->amazonId;
    }

    public function setAmazonId(string $amazonId): self
    {
        $this->amazonId = $amazonId;

        return $this;
    }


    // TCMSFieldDecimal
    public function getValue(): float
    {
        return $this->value;
    }

    public function setValue(float $value): self
    {
        $this->value = $value;

        return $this;
    }


    // TCMSFieldNumber
    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }


    // TCMSFieldNumber
    public function getRequestMode(): int
    {
        return $this->requestMode;
    }

    public function setRequestMode(int $requestMode): self
    {
        $this->requestMode = $requestMode;

        return $this;
    }


    // TCMSFieldBoolean
    public function isCaptureNow(): bool
    {
        return $this->captureNow;
    }

    public function setCaptureNow(bool $captureNow): self
    {
        $this->captureNow = $captureNow;

        return $this;
    }


    // TCMSFieldExtendedLookup
    public function getPkgShopPaymentTransaction(): ?PkgShopPaymentTransaction
    {
        return $this->pkgShopPaymentTransaction;
    }

    public function setPkgShopPaymentTransaction(?PkgShopPaymentTransaction $pkgShopPaymentTransaction): self
    {
        $this->pkgShopPaymentTransaction = $pkgShopPaymentTransaction;

        return $this;
    }


}
