<?php

namespace ChameleonSystem\ShopBundle\Entity\Payment;

use ChameleonSystem\DataAccessBundle\Entity\CorePortal\CmsPortal;

class ShopPaymentHandlerParameter
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var ShopPaymentHandler|null - Belongs to payment handler */
        private ?ShopPaymentHandler $shopPaymentHandler = null,
        // TCMSFieldVarchar
        /** @var string - Display name */
        private string $name = '',
        // TCMSFieldOption
        /** @var string - Type */
        private string $type = 'common',
        // TCMSFieldVarchar
        /** @var string - System name */
        private string $systemname = '',
        // TCMSFieldWYSIWYG
        /** @var string - Description */
        private string $description = '',
        // TCMSFieldText
        /** @var string - Value */
        private string $value = '',
        // TCMSFieldLookup
        /** @var CmsPortal|null - Applies to this portal only */
        private ?CmsPortal $cmsPortal = null
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
    public function getShopPaymentHandler(): ?ShopPaymentHandler
    {
        return $this->shopPaymentHandler;
    }

    public function setShopPaymentHandler(?ShopPaymentHandler $shopPaymentHandler): self
    {
        $this->shopPaymentHandler = $shopPaymentHandler;

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

    // TCMSFieldVarchar
    public function getSystemname(): string
    {
        return $this->systemname;
    }

    public function setSystemname(string $systemname): self
    {
        $this->systemname = $systemname;

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

    // TCMSFieldLookup
    public function getCmsPortal(): ?CmsPortal
    {
        return $this->cmsPortal;
    }

    public function setCmsPortal(?CmsPortal $cmsPortal): self
    {
        $this->cmsPortal = $cmsPortal;

        return $this;
    }
}
