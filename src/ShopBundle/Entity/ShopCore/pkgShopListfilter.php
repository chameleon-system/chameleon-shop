<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

use ChameleonSystem\ShopBundle\Entity\ShopCore\pkgShopListfilterItem;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class pkgShopListfilter {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldVarchar
/** @var string - Name */
private string $Name = '', 
    // TCMSFieldVarchar
/** @var string - Title to be shown on top of the filter on the website */
private string $Title = '', 
    // TCMSFieldWYSIWYG
/** @var string - Description text shown on top of the filter */
private string $Introtext = '', 
    // TCMSFieldPropertyTable
/** @var Collection<int, pkgShopListfilterItem> - List filter entries */
private Collection $PkgShopListfilterItemCollection = new ArrayCollection()
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


  
    // TCMSFieldVarchar
public function gettitle(): string
{
    return $this->Title;
}
public function settitle(string $Title): self
{
    $this->Title = $Title;

    return $this;
}


  
    // TCMSFieldWYSIWYG
public function getintrotext(): string
{
    return $this->Introtext;
}
public function setintrotext(string $Introtext): self
{
    $this->Introtext = $Introtext;

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, pkgShopListfilterItem>
*/
public function getPkgShopListfilterItemCollection(): Collection
{
    return $this->PkgShopListfilterItemCollection;
}

public function addPkgShopListfilterItemCollection(pkgShopListfilterItem $PkgShopListfilterItem): self
{
    if (!$this->PkgShopListfilterItemCollection->contains($PkgShopListfilterItem)) {
        $this->PkgShopListfilterItemCollection->add($PkgShopListfilterItem);
        $PkgShopListfilterItem->setPkgShopListfilter($this);
    }

    return $this;
}

public function removePkgShopListfilterItemCollection(pkgShopListfilterItem $PkgShopListfilterItem): self
{
    if ($this->PkgShopListfilterItemCollection->removeElement($PkgShopListfilterItem)) {
        // set the owning side to null (unless already changed)
        if ($PkgShopListfilterItem->getPkgShopListfilter() === $this) {
            $PkgShopListfilterItem->setPkgShopListfilter(null);
        }
    }

    return $this;
}


  
}
