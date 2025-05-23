<?php

namespace ChameleonSystem\ImageHotspotBundle\Entity;

use ChameleonSystem\DataAccessBundle\Entity\CoreMedia\CmsMedia;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class PkgImageHotspotItem
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var PkgImageHotspot|null - Belongs to image hotspot */
        private ?PkgImageHotspot $pkgImageHotspot = null,
        // TCMSFieldVarchar
        /** @var string - Alternative text for image */
        private string $name = '',
        // TCMSFieldBoolean
        /** @var bool - Active */
        private bool $active = false,
        // TCMSFieldPosition
        /** @var int - Position */
        private int $position = 0,
        // ChameleonSystem\ImageCropBundle\Bridge\Chameleon\Field\TCMSFieldMediaWithImageCrop
        /** @var CmsMedia|null - Image */
        private ?CmsMedia $cmsMedia = null,
        // ChameleonSystem\ImageCropBundle\Bridge\Chameleon\Field\TCMSFieldMediaWithImageCrop
        /** @var CmsMedia|null - Image */
        private ?CmsMedia $cmsMediaIdImageCrop = null,
        // TCMSFieldPropertyTable
        /** @var Collection<int, PkgImageHotspotItemSpot> - Hotspots and linked areas */
        private Collection $pkgImageHotspotItemSpotCollection = new ArrayCollection(),
        // TCMSFieldPropertyTable
        /** @var Collection<int, PkgImageHotspotItemMarker> - Hotspots with image */
        private Collection $pkgImageHotspotItemMarkerCollection = new ArrayCollection()
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
    public function getPkgImageHotspot(): ?PkgImageHotspot
    {
        return $this->pkgImageHotspot;
    }

    public function setPkgImageHotspot(?PkgImageHotspot $pkgImageHotspot): self
    {
        $this->pkgImageHotspot = $pkgImageHotspot;

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

    // TCMSFieldPosition
    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    // ChameleonSystem\ImageCropBundle\Bridge\Chameleon\Field\TCMSFieldMediaWithImageCrop
    public function getCmsMedia(): ?CmsMedia
    {
        return $this->cmsMedia;
    }

    public function setCmsMedia(?CmsMedia $cmsMedia): self
    {
        $this->cmsMedia = $cmsMedia;

        return $this;
    }

    // ChameleonSystem\ImageCropBundle\Bridge\Chameleon\Field\TCMSFieldMediaWithImageCrop
    public function getCmsMediaIdImageCrop(): ?CmsMedia
    {
        return $this->cmsMediaIdImageCrop;
    }

    public function setCmsMediaIdImageCrop(?CmsMedia $cmsMediaIdImageCrop): self
    {
        $this->cmsMediaIdImageCrop = $cmsMediaIdImageCrop;

        return $this;
    }

    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, PkgImageHotspotItemSpot>
     */
    public function getPkgImageHotspotItemSpotCollection(): Collection
    {
        return $this->pkgImageHotspotItemSpotCollection;
    }

    public function addPkgImageHotspotItemSpotCollection(PkgImageHotspotItemSpot $pkgImageHotspotItemSpot): self
    {
        if (!$this->pkgImageHotspotItemSpotCollection->contains($pkgImageHotspotItemSpot)) {
            $this->pkgImageHotspotItemSpotCollection->add($pkgImageHotspotItemSpot);
            $pkgImageHotspotItemSpot->setPkgImageHotspotItem($this);
        }

        return $this;
    }

    public function removePkgImageHotspotItemSpotCollection(PkgImageHotspotItemSpot $pkgImageHotspotItemSpot): self
    {
        if ($this->pkgImageHotspotItemSpotCollection->removeElement($pkgImageHotspotItemSpot)) {
            // set the owning side to null (unless already changed)
            if ($pkgImageHotspotItemSpot->getPkgImageHotspotItem() === $this) {
                $pkgImageHotspotItemSpot->setPkgImageHotspotItem(null);
            }
        }

        return $this;
    }

    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, PkgImageHotspotItemMarker>
     */
    public function getPkgImageHotspotItemMarkerCollection(): Collection
    {
        return $this->pkgImageHotspotItemMarkerCollection;
    }

    public function addPkgImageHotspotItemMarkerCollection(PkgImageHotspotItemMarker $pkgImageHotspotItemMarker): self
    {
        if (!$this->pkgImageHotspotItemMarkerCollection->contains($pkgImageHotspotItemMarker)) {
            $this->pkgImageHotspotItemMarkerCollection->add($pkgImageHotspotItemMarker);
            $pkgImageHotspotItemMarker->setPkgImageHotspotItem($this);
        }

        return $this;
    }

    public function removePkgImageHotspotItemMarkerCollection(PkgImageHotspotItemMarker $pkgImageHotspotItemMarker
    ): self {
        if ($this->pkgImageHotspotItemMarkerCollection->removeElement($pkgImageHotspotItemMarker)) {
            // set the owning side to null (unless already changed)
            if ($pkgImageHotspotItemMarker->getPkgImageHotspotItem() === $this) {
                $pkgImageHotspotItemMarker->setPkgImageHotspotItem(null);
            }
        }

        return $this;
    }
}
