<?php
namespace ChameleonSystem\ImageHotspotBundle\Entity;

use ChameleonSystem\DataAccessBundle\Entity\CorePagedef\cmsTplModuleInstance;
use ChameleonSystem\ImageHotspotBundle\Entity\pkgImageHotspotItem;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class pkgImageHotspot {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var cmsTplModuleInstance|null - Belongs to module instance */
private ?cmsTplModuleInstance $CmsTplModuleInstance = null
, 
    // TCMSFieldVarchar
/** @var string - Headline */
private string $Name = '', 
    // TCMSFieldNumber
/** @var int - How long should an image be displayed (in seconds)? */
private int $AutoSlideTime = 0, 
    // TCMSFieldPropertyTable
/** @var Collection<int, pkgImageHotspotItem> - Images */
private Collection $PkgImageHotspotItemCollection = new ArrayCollection()
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
public function getCmsTplModuleInstance(): ?cmsTplModuleInstance
{
    return $this->CmsTplModuleInstance;
}

public function setCmsTplModuleInstance(?cmsTplModuleInstance $CmsTplModuleInstance): self
{
    $this->CmsTplModuleInstance = $CmsTplModuleInstance;

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


  
    // TCMSFieldNumber
public function getautoSlideTime(): int
{
    return $this->AutoSlideTime;
}
public function setautoSlideTime(int $AutoSlideTime): self
{
    $this->AutoSlideTime = $AutoSlideTime;

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, pkgImageHotspotItem>
*/
public function getPkgImageHotspotItemCollection(): Collection
{
    return $this->PkgImageHotspotItemCollection;
}

public function addPkgImageHotspotItemCollection(pkgImageHotspotItem $PkgImageHotspotItem): self
{
    if (!$this->PkgImageHotspotItemCollection->contains($PkgImageHotspotItem)) {
        $this->PkgImageHotspotItemCollection->add($PkgImageHotspotItem);
        $PkgImageHotspotItem->setPkgImageHotspot($this);
    }

    return $this;
}

public function removePkgImageHotspotItemCollection(pkgImageHotspotItem $PkgImageHotspotItem): self
{
    if ($this->PkgImageHotspotItemCollection->removeElement($PkgImageHotspotItem)) {
        // set the owning side to null (unless already changed)
        if ($PkgImageHotspotItem->getPkgImageHotspot() === $this) {
            $PkgImageHotspotItem->setPkgImageHotspot(null);
        }
    }

    return $this;
}


  
}
