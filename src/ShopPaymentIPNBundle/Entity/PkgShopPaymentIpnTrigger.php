<?php

namespace ChameleonSystem\ShopPaymentIPNBundle\Entity;

use ChameleonSystem\ShopBundle\Entity\Payment\ShopPaymentHandlerGroup;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class PkgShopPaymentIpnTrigger
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var ShopPaymentHandlerGroup|null - Belongs to payment provider */
        private ?ShopPaymentHandlerGroup $shopPaymentHandlerGroup = null,
        // TCMSFieldPropertyTable
        /** @var Collection<int, PkgShopPaymentIpnMessageTrigger> - */
        private Collection $pkgShopPaymentIpnMessageTriggerCollection = new ArrayCollection(),
        // TCMSFieldBoolean
        /** @var bool - Active */
        private bool $active = false,
        // TCMSFieldURL
        /** @var string - Target URL */
        private string $targetUrl = '',
        // TCMSFieldNumber
        /** @var int - Timeout */
        private int $timeoutSeconds = 30,
        // TCMSFieldLookupMultiselect
        /** @var Collection<int, PkgShopPaymentIpnStatus> - Status codes to be forwarded */
        private Collection $pkgShopPaymentIpnStatusCollection = new ArrayCollection()
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
            $pkgShopPaymentIpnMessageTrigger->setPkgShopPaymentIpnTrigger($this);
        }

        return $this;
    }

    public function removePkgShopPaymentIpnMessageTriggerCollection(
        PkgShopPaymentIpnMessageTrigger $pkgShopPaymentIpnMessageTrigger
    ): self {
        if ($this->pkgShopPaymentIpnMessageTriggerCollection->removeElement($pkgShopPaymentIpnMessageTrigger)) {
            // set the owning side to null (unless already changed)
            if ($pkgShopPaymentIpnMessageTrigger->getPkgShopPaymentIpnTrigger() === $this) {
                $pkgShopPaymentIpnMessageTrigger->setPkgShopPaymentIpnTrigger(null);
            }
        }

        return $this;
    }

    // TCMSFieldBoolean
    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    // TCMSFieldURL
    public function getTargetUrl(): string
    {
        return $this->targetUrl;
    }

    public function setTargetUrl(string $targetUrl): self
    {
        $this->targetUrl = $targetUrl;

        return $this;
    }

    // TCMSFieldNumber
    public function getTimeoutSeconds(): int
    {
        return $this->timeoutSeconds;
    }

    public function setTimeoutSeconds(int $timeoutSeconds): self
    {
        $this->timeoutSeconds = $timeoutSeconds;

        return $this;
    }

    // TCMSFieldLookupMultiselect

    /**
     * @return Collection<int, PkgShopPaymentIpnStatus>
     */
    public function getPkgShopPaymentIpnStatusCollection(): Collection
    {
        return $this->pkgShopPaymentIpnStatusCollection;
    }

    public function addPkgShopPaymentIpnStatusCollection(PkgShopPaymentIpnStatus $pkgShopPaymentIpnStatusMlt): self
    {
        if (!$this->pkgShopPaymentIpnStatusCollection->contains($pkgShopPaymentIpnStatusMlt)) {
            $this->pkgShopPaymentIpnStatusCollection->add($pkgShopPaymentIpnStatusMlt);
            $pkgShopPaymentIpnStatusMlt->set($this);
        }

        return $this;
    }

    public function removePkgShopPaymentIpnStatusCollection(PkgShopPaymentIpnStatus $pkgShopPaymentIpnStatusMlt): self
    {
        if ($this->pkgShopPaymentIpnStatusCollection->removeElement($pkgShopPaymentIpnStatusMlt)) {
            // set the owning side to null (unless already changed)
            if ($pkgShopPaymentIpnStatusMlt->get() === $this) {
                $pkgShopPaymentIpnStatusMlt->set(null);
            }
        }

        return $this;
    }
}
