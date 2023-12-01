<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

use ChameleonSystem\DataAccessBundle\Entity\CorePagedef\cmsTplModuleInstance;
use ChameleonSystem\ShopBundle\Entity\ShopCore\shopSystemInfo;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class shopSystemInfoModuleConfig {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var cmsTplModuleInstance|null - Belongs to module instance */
private ?cmsTplModuleInstance $CmsTplModuleInstance = null
, 
    // TCMSFieldVarchar
/** @var string - Optional title */
private string $Name = '', 
    // TCMSFieldWYSIWYG
/** @var string - Optional introduction text */
private string $Intro = '', 
    // TCMSFieldLookupMultiselectCheckboxes
/** @var Collection<int, shopSystemInfo> - Shop info pages to be displayed */
private Collection $ShopSystemInfoCollection = new ArrayCollection()
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


  
    // TCMSFieldWYSIWYG
public function getintro(): string
{
    return $this->Intro;
}
public function setintro(string $Intro): self
{
    $this->Intro = $Intro;

    return $this;
}


  
    // TCMSFieldLookupMultiselectCheckboxes
/**
* @return Collection<int, shopSystemInfo>
*/
public function getShopSystemInfoCollection(): Collection
{
    return $this->ShopSystemInfoCollection;
}

public function addShopSystemInfoCollection(shopSystemInfo $ShopSystemInfoMlt): self
{
    if (!$this->ShopSystemInfoCollection->contains($ShopSystemInfoMlt)) {
        $this->ShopSystemInfoCollection->add($ShopSystemInfoMlt);
        $ShopSystemInfoMlt->set($this);
    }

    return $this;
}

public function removeShopSystemInfoCollection(shopSystemInfo $ShopSystemInfoMlt): self
{
    if ($this->ShopSystemInfoCollection->removeElement($ShopSystemInfoMlt)) {
        // set the owning side to null (unless already changed)
        if ($ShopSystemInfoMlt->get() === $this) {
            $ShopSystemInfoMlt->set(null);
        }
    }

    return $this;
}


  
}
