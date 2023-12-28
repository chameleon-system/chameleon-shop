<?php

namespace ChameleonSystem\ShopBundle\Entity\Payment;

use ChameleonSystem\DataAccessBundle\Entity\CorePortal\CmsPortal;

class ShopPaymentHandlerGroupConfig
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var ShopPaymentHandlerGroup|null - Belongs to */
        private ?ShopPaymentHandlerGroup $shopPaymentHandlerGroup = null
        ,
        // TCMSFieldVarchar
        /** @var string - Name */
        private string $name = '',
        // TCMSFieldOption
        /** @var string - Type */
        private string $type = 'common',
        // TCMSFieldExtendedLookup
        /** @var CmsPortal|null - Portal */
        private ?CmsPortal $cmsPortal = null
        ,
        // TCMSFieldText
        /** @var string - Value */
        private string $value = '',
        // TCMSFieldWYSIWYG
        /** @var string - Description */
        private string $description = ''
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
    public function getShopPaymentHandlerGroup(): ?ShopPaymentHandlerGroup
    {
        return $this->shopPaymentHandlerGroup;
    }

    public function setShopPaymentHandlerGroup(?ShopPaymentHandlerGroup $shopPaymentHandlerGroup): self
    {
        $this->shopPaymentHandlerGroup = $shopPaymentHandlerGroup;

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
    public function getCmsPortal(): ?CmsPortal
    {
        return $this->cmsPortal;
    }

    public function setCmsPortal(?CmsPortal $cmsPortal): self
    {
        $this->cmsPortal = $cmsPortal;

        return $this;
    }


    // TCMSFieldText
    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }


    // TCMSFieldWYSIWYG
    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }


}
