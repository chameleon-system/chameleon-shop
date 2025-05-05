<?php

namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

use ChameleonSystem\DataAccessBundle\Entity\CorePagedef\CmsTplModuleInstance;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ShopSystemInfoModuleConfig
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var CmsTplModuleInstance|null - Belongs to module instance */
        private ?CmsTplModuleInstance $cmsTplModuleInstance = null,
        // TCMSFieldVarchar
        /** @var string - Optional title */
        private string $name = '',
        // TCMSFieldWYSIWYG
        /** @var string - Optional introduction text */
        private string $intro = '',
        // TCMSFieldLookupMultiselectCheckboxes
        /** @var Collection<int, ShopSystemInfo> - Shop info pages to be displayed */
        private Collection $shopSystemInfoCollection = new ArrayCollection()
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
    public function getCmsTplModuleInstance(): ?CmsTplModuleInstance
    {
        return $this->cmsTplModuleInstance;
    }

    public function setCmsTplModuleInstance(?CmsTplModuleInstance $cmsTplModuleInstance): self
    {
        $this->cmsTplModuleInstance = $cmsTplModuleInstance;

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

    // TCMSFieldWYSIWYG
    public function getIntro(): string
    {
        return $this->intro;
    }

    public function setIntro(string $intro): self
    {
        $this->intro = $intro;

        return $this;
    }

    // TCMSFieldLookupMultiselectCheckboxes

    /**
     * @return Collection<int, ShopSystemInfo>
     */
    public function getShopSystemInfoCollection(): Collection
    {
        return $this->shopSystemInfoCollection;
    }

    public function addShopSystemInfoCollection(ShopSystemInfo $shopSystemInfoMlt): self
    {
        if (!$this->shopSystemInfoCollection->contains($shopSystemInfoMlt)) {
            $this->shopSystemInfoCollection->add($shopSystemInfoMlt);
            $shopSystemInfoMlt->set($this);
        }

        return $this;
    }

    public function removeShopSystemInfoCollection(ShopSystemInfo $shopSystemInfoMlt): self
    {
        if ($this->shopSystemInfoCollection->removeElement($shopSystemInfoMlt)) {
            // set the owning side to null (unless already changed)
            if ($shopSystemInfoMlt->get() === $this) {
                $shopSystemInfoMlt->set(null);
            }
        }

        return $this;
    }
}
