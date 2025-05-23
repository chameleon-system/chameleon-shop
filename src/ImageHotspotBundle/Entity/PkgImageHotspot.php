<?php

namespace ChameleonSystem\ImageHotspotBundle\Entity;

use ChameleonSystem\DataAccessBundle\Entity\CorePagedef\CmsTplModuleInstance;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class PkgImageHotspot
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var CmsTplModuleInstance|null - Belongs to module instance */
        private ?CmsTplModuleInstance $cmsTplModuleInstance = null,
        // TCMSFieldVarchar
        /** @var string - Headline */
        private string $name = '',
        // TCMSFieldNumber
        /** @var int - How long should an image be displayed (in seconds)? */
        private int $autoSlideTime = 0,
        // TCMSFieldPropertyTable
        /** @var Collection<int, PkgImageHotspotItem> - Images */
        private Collection $pkgImageHotspotItemCollection = new ArrayCollection()
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

    // TCMSFieldNumber
    public function getAutoSlideTime(): int
    {
        return $this->autoSlideTime;
    }

    public function setAutoSlideTime(int $autoSlideTime): self
    {
        $this->autoSlideTime = $autoSlideTime;

        return $this;
    }

    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, PkgImageHotspotItem>
     */
    public function getPkgImageHotspotItemCollection(): Collection
    {
        return $this->pkgImageHotspotItemCollection;
    }

    public function addPkgImageHotspotItemCollection(PkgImageHotspotItem $pkgImageHotspotItem): self
    {
        if (!$this->pkgImageHotspotItemCollection->contains($pkgImageHotspotItem)) {
            $this->pkgImageHotspotItemCollection->add($pkgImageHotspotItem);
            $pkgImageHotspotItem->setPkgImageHotspot($this);
        }

        return $this;
    }

    public function removePkgImageHotspotItemCollection(PkgImageHotspotItem $pkgImageHotspotItem): self
    {
        if ($this->pkgImageHotspotItemCollection->removeElement($pkgImageHotspotItem)) {
            // set the owning side to null (unless already changed)
            if ($pkgImageHotspotItem->getPkgImageHotspot() === $this) {
                $pkgImageHotspotItem->setPkgImageHotspot(null);
            }
        }

        return $this;
    }
}
