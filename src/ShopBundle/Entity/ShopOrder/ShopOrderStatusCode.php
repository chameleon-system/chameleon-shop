<?php

namespace ChameleonSystem\ShopBundle\Entity\ShopOrder;

use ChameleonSystem\DataAccessBundle\Entity\Core\DataMailProfile;
use ChameleonSystem\ShopBundle\Entity\ShopCore\Shop;
use ChameleonSystem\ShopPaymentTransactionBundle\Entity\PkgShopPaymentTransactionType;

class ShopOrderStatusCode
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldVarchar
        /** @var string - Name */
        private string $name = '',
        // TCMSFieldLookupParentID
        /** @var Shop|null - Belongs to shop */
        private ?Shop $shop = null
        ,
        // TCMSFieldBoolean
        /** @var bool - Send status notification via email */
        private bool $sendMailNotification = true,
        // TCMSFieldVarchar
        /** @var string - System name / merchandise management code */
        private string $systemName = '',
        // TCMSFieldExtendedLookup
        /** @var PkgShopPaymentTransactionType|null - Run following transaction, if status is executed */
        private ?PkgShopPaymentTransactionType $pkgShopPaymentTransactionType = null
        ,
        // TCMSFieldLookup
        /** @var DataMailProfile|null - Email profile */
        private ?DataMailProfile $dataMailProfile = null
        ,
        // TCMSFieldWYSIWYG
        /** @var string - Status text */
        private string $infoText = ''
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

    // TCMSFieldVarchar
    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }


    // TCMSFieldLookupParentID
    public function getShop(): ?Shop
    {
        return $this->shop;
    }

    public function setShop(?Shop $shop): self
    {
        $this->shop = $shop;

        return $this;
    }


    // TCMSFieldBoolean
    public function isSendMailNotification(): bool
    {
        return $this->sendMailNotification;
    }

    public function setSendMailNotification(bool $sendMailNotification): self
    {
        $this->sendMailNotification = $sendMailNotification;

        return $this;
    }


    // TCMSFieldVarchar
    public function getSystemName(): string
    {
        return $this->systemName;
    }

    public function setSystemName(string $systemName): self
    {
        $this->systemName = $systemName;

        return $this;
    }


    // TCMSFieldExtendedLookup
    public function getPkgShopPaymentTransactionType(): ?PkgShopPaymentTransactionType
    {
        return $this->pkgShopPaymentTransactionType;
    }

    public function setPkgShopPaymentTransactionType(?PkgShopPaymentTransactionType $pkgShopPaymentTransactionType
    ): self {
        $this->pkgShopPaymentTransactionType = $pkgShopPaymentTransactionType;

        return $this;
    }


    // TCMSFieldLookup
    public function getDataMailProfile(): ?DataMailProfile
    {
        return $this->dataMailProfile;
    }

    public function setDataMailProfile(?DataMailProfile $dataMailProfile): self
    {
        $this->dataMailProfile = $dataMailProfile;

        return $this;
    }


    // TCMSFieldWYSIWYG
    public function getInfoText(): string
    {
        return $this->infoText;
    }

    public function setInfoText(string $infoText): self
    {
        $this->infoText = $infoText;

        return $this;
    }


}
