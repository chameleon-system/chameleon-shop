<?php
namespace ChameleonSystem\ImageHotspotBundle\Entity;

use ChameleonSystem\ImageHotspotBundle\Entity\pkgImageHotspot;
use ChameleonSystem\DataAccessBundle\Entity\CoreMedia\cmsMedia;
use ChameleonSystem\ImageHotspotBundle\Entity\pkgImageHotspotItemSpot;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use ChameleonSystem\ImageHotspotBundle\Entity\pkgImageHotspotItemMarker;

class pkgImageHotspotItem {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var pkgImageHotspot|null - Belongs to image hotspot */
private ?pkgImageHotspot $PkgImageHotspot = null
, 
    // TCMSFieldVarchar
/** @var string - Alternative text for image */
private string $Name = '', 
    // TCMSFieldBoolean
/** @var bool - Active */
private bool $Active = false, 
    // TCMSFieldPosition
/** @var int - Position */
private int $Position = 0, 
    // ChameleonSystem\ImageCropBundle\Bridge\Chameleon\Field\TCMSFieldMediaWithImageCrop
/** @var cmsMedia|null - Image */
private ?cmsMedia $CmsMedia = null
,
// ChameleonSystem\ImageCropBundle\Bridge\Chameleon\Field\TCMSFieldMediaWithImageCrop
/** @var cmsMedia|null - Image */
private ?cmsMedia $CmsMediaIdImageCrop = null
, 
    // TCMSFieldPropertyTable
/** @var Collection<int, pkgImageHotspotItemSpot> - Hotspots and linked areas */
private Collection $PkgImageHotspotItemSpotCollection = new ArrayCollection()
, 
    // TCMSFieldPropertyTable
/** @var Collection<int, pkgImageHotspotItemMarker> - Hotspots with image */
private Collection $PkgImageHotspotItemMarkerCollection = new ArrayCollection()
  ) {}

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
public function getPkgImageHotspot(): ?pkgImageHotspot
{
    return $this->PkgImageHotspot;
}

public function setPkgImageHotspot(?pkgImageHotspot $PkgImageHotspot): self
{
    $this->PkgImageHotspot = $PkgImageHotspot;

    return $this;
}


  
    // TCMSFieldVarchar
public function getname(): string
{
    return $this->Name;
}
public function setname(string $Name): self
{
    $this->Name = $Name;

    return $this;
}


  
    // TCMSFieldBoolean
public function isactive(): bool
{
    return $this->Active;
}
public function setactive(bool $Active): self
{
    $this->Active = $Active;

    return $this;
}


  
    // TCMSFieldPosition
public function getposition(): int
{
    return $this->Position;
}
public function setposition(int $Position): self
{
    $this->Position = $Position;

    return $this;
}


  
    // ChameleonSystem\ImageCropBundle\Bridge\Chameleon\Field\TCMSFieldMediaWithImageCrop
public function getCmsMedia(): ?cmsMedia
{
    return $this->CmsMedia;
}

public function setCmsMedia(?cmsMedia $CmsMedia): self
{
    $this->CmsMedia = $CmsMedia;

    return $this;
}
// ChameleonSystem\ImageCropBundle\Bridge\Chameleon\Field\TCMSFieldMediaWithImageCrop
public function getCmsMediaIdImageCrop(): ?cmsMedia
{
    return $this->CmsMediaIdImageCrop;
}

public function setCmsMediaIdImageCrop(?cmsMedia $CmsMediaIdImageCrop): self
{
    $this->CmsMediaIdImageCrop = $CmsMediaIdImageCrop;

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, pkgImageHotspotItemSpot>
*/
public function getPkgImageHotspotItemSpotCollection(): Collection
{
    return $this->PkgImageHotspotItemSpotCollection;
}

public function addPkgImageHotspotItemSpotCollection(pkgImageHotspotItemSpot $PkgImageHotspotItemSpot): self
{
    if (!$this->PkgImageHotspotItemSpotCollection->contains($PkgImageHotspotItemSpot)) {
        $this->PkgImageHotspotItemSpotCollection->add($PkgImageHotspotItemSpot);
        $PkgImageHotspotItemSpot->setPkgImageHotspotItem($this);
    }

    return $this;
}

public function removePkgImageHotspotItemSpotCollection(pkgImageHotspotItemSpot $PkgImageHotspotItemSpot): self
{
    if ($this->PkgImageHotspotItemSpotCollection->removeElement($PkgImageHotspotItemSpot)) {
        // set the owning side to null (unless already changed)
        if ($PkgImageHotspotItemSpot->getPkgImageHotspotItem() === $this) {
            $PkgImageHotspotItemSpot->setPkgImageHotspotItem(null);
        }
    }

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, pkgImageHotspotItemMarker>
*/
public function getPkgImageHotspotItemMarkerCollection(): Collection
{
    return $this->PkgImageHotspotItemMarkerCollection;
}

public function addPkgImageHotspotItemMarkerCollection(pkgImageHotspotItemMarker $PkgImageHotspotItemMarker): self
{
    if (!$this->PkgImageHotspotItemMarkerCollection->contains($PkgImageHotspotItemMarker)) {
        $this->PkgImageHotspotItemMarkerCollection->add($PkgImageHotspotItemMarker);
        $PkgImageHotspotItemMarker->setPkgImageHotspotItem($this);
    }

    return $this;
}

public function removePkgImageHotspotItemMarkerCollection(pkgImageHotspotItemMarker $PkgImageHotspotItemMarker): self
{
    if ($this->PkgImageHotspotItemMarkerCollection->removeElement($PkgImageHotspotItemMarker)) {
        // set the owning side to null (unless already changed)
        if ($PkgImageHotspotItemMarker->getPkgImageHotspotItem() === $this) {
            $PkgImageHotspotItemMarker->setPkgImageHotspotItem(null);
        }
    }

    return $this;
}


  
}
