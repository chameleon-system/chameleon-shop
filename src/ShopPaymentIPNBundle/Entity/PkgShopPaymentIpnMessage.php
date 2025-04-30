<?php

namespace ChameleonSystem\ShopPaymentIPNBundle\Entity;

use ChameleonSystem\DataAccessBundle\Entity\CorePortal\CmsPortal;
use ChameleonSystem\ShopBundle\Entity\Payment\ShopPaymentHandlerGroup;
use ChameleonSystem\ShopBundle\Entity\ShopOrder\ShopOrder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class PkgShopPaymentIpnMessage
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldPropertyTable
        /** @var Collection<int, PkgShopPaymentIpnMessageTrigger> - Forwarding logs */
        private Collection $pkgShopPaymentIpnMessageTriggerCollection = new ArrayCollection(),
        // TCMSFieldExtendedLookup
        /** @var CmsPortal|null - Activated via this portal */
        private ?CmsPortal $cmsPortal = null,
        // TCMSFieldLookupParentID
        /** @var ShopOrder|null - Belongs to order (ID) */
        private ?ShopOrder $shopOrder = null,
        // TCMSFieldLookupParentID
        /** @var ShopPaymentHandlerGroup|null - Payment provider */
        private ?ShopPaymentHandlerGroup $shopPaymentHandlerGroup = null,
        // TCMSFieldCreatedTimestamp
        /** @var \DateTime|null - Date */
        private ?\DateTime $datecreated = null,
        // TCMSFieldExtendedLookup
        /** @var PkgShopPaymentIpnStatus|null - Status */
        private ?PkgShopPaymentIpnStatus $pkgShopPaymentIpnStatus = null,
        // TCMSFieldBoolean
        /** @var bool - Processed successfully */
        private bool $success = false,
        // TCMSFieldBoolean
        /** @var bool - Processed message */
        private bool $completed = false,
        // TCMSFieldVarchar
        /** @var string - Type of error */
        private string $errorType = '',
        // TCMSFieldVarchar
        /** @var string - IP */
        private string $ip = '',
        // TCMSFieldVarchar
        /** @var string - Request URL */
        private string $requestUrl = '',
        // TCMSFieldBlob
        /** @var object|null - Payload */
        private ?object $payload = null,
        // TCMSFieldText
        /** @var string - Error details */
        private string $errors = ''
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
    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, PkgShopPaymentIpnMessageTrigger>
     */
    public function getPkgShopPaymentIpnMessageTriggerCollection(): Collection
    {
        return $this->pkgShopPaymentIpnMessageTriggerCollection;
    }

    public function addPkgShopPaymentIpnMessageTriggerCollection(
        PkgShopPaymentIpnMessageTrigger $pkgShopPaymentIpnMessageTrigger
    ): self {
        if (!$this->pkgShopPaymentIpnMessageTriggerCollection->contains($pkgShopPaymentIpnMessageTrigger)) {
            $this->pkgShopPaymentIpnMessageTriggerCollection->add($pkgShopPaymentIpnMessageTrigger);
            $pkgShopPaymentIpnMessageTrigger->setPkgShopPaymentIpnMessage($this);
        }

        return $this;
    }

    public function removePkgShopPaymentIpnMessageTriggerCollection(
        PkgShopPaymentIpnMessageTrigger $pkgShopPaymentIpnMessageTrigger
    ): self {
        if ($this->pkgShopPaymentIpnMessageTriggerCollection->removeElement($pkgShopPaymentIpnMessageTrigger)) {
            // set the owning side to null (unless already changed)
            if ($pkgShopPaymentIpnMessageTrigger->getPkgShopPaymentIpnMessage() === $this) {
                $pkgShopPaymentIpnMessageTrigger->setPkgShopPaymentIpnMessage(null);
            }
        }

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

    // TCMSFieldCreatedTimestamp
    public function getDatecreated(): ?\DateTime
    {
        return $this->datecreated;
    }

    public function setDatecreated(?\DateTime $datecreated): self
    {
        $this->datecreated = $datecreated;

        return $this;
    }

    // TCMSFieldExtendedLookup
    public function getPkgShopPaymentIpnStatus(): ?PkgShopPaymentIpnStatus
    {
        return $this->pkgShopPaymentIpnStatus;
    }

    public function setPkgShopPaymentIpnStatus(?PkgShopPaymentIpnStatus $pkgShopPaymentIpnStatus): self
    {
        $this->pkgShopPaymentIpnStatus = $pkgShopPaymentIpnStatus;

        return $this;
    }

    // TCMSFieldBoolean
    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function setSuccess(bool $success): self
    {
        $this->success = $success;

        return $this;
    }

    // TCMSFieldBoolean
    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function setCompleted(bool $completed): self
    {
        $this->completed = $completed;

        return $this;
    }

    // TCMSFieldVarchar
    public function getErrorType(): string
    {
        return $this->errorType;
    }

    public function setErrorType(string $errorType): self
    {
        $this->errorType = $errorType;

        return $this;
    }

    // TCMSFieldVarchar
    public function getIp(): string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    // TCMSFieldVarchar
    public function getRequestUrl(): string
    {
        return $this->requestUrl;
    }

    public function setRequestUrl(string $requestUrl): self
    {
        $this->requestUrl = $requestUrl;

        return $this;
    }

    // TCMSFieldBlob
    public function getPayload(): ?object
    {
        return $this->payload;
    }

    public function setPayload(?object $payload): self
    {
        $this->payload = $payload;

        return $this;
    }

    // TCMSFieldText
    public function getErrors(): string
    {
        return $this->errors;
    }

    public function setErrors(string $errors): self
    {
        $this->errors = $errors;

        return $this;
    }
}
