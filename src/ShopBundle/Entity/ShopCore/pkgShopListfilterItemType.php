<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

use ChameleonSystem\DataAccessBundle\Entity\CoreTableConfiguration\cmsFieldConf;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class pkgShopListfilterItemType {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldVarchar
/** @var string - Name */
private string $Name = '', 
    // TCMSFieldVarchar
/** @var string - Filter element class */
private string $Class = '', 
    // TCMSFieldVarchar
/** @var string - Class subtypes of the filter element */
private string $ClassSubtype = '', 
    // TCMSFieldOption
/** @var string - Class type of the filter element */
private string $ClassType = 'Core', 
    // TCMSFieldVarchar
/** @var string - View of the filter element */
private string $View = '', 
    // TCMSFieldOption
/** @var string - Class type of the view for the filter element */
private string $ViewClassType = 'Core', 
    // TCMSFieldLookupMultiselectCheckboxesSelectFieldsFromTable
/** @var Collection<int, cmsFieldConf> - Available fields of the filter element */
private Collection $CmsFieldConfCollection = new ArrayCollection()
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
public function getclass(): string
{
    return $this->Class;
}
public function setclass(string $Class): self
{
    $this->Class = $Class;

    return $this;
}


  
    // TCMSFieldVarchar
public function getclassSubtype(): string
{
    return $this->ClassSubtype;
}
public function setclassSubtype(string $ClassSubtype): self
{
    $this->ClassSubtype = $ClassSubtype;

    return $this;
}


  
    // TCMSFieldOption
public function getclassType(): string
{
    return $this->ClassType;
}
public function setclassType(string $ClassType): self
{
    $this->ClassType = $ClassType;

    return $this;
}


  
    // TCMSFieldVarchar
public function getview(): string
{
    return $this->View;
}
public function setview(string $View): self
{
    $this->View = $View;

    return $this;
}


  
    // TCMSFieldOption
public function getviewClassType(): string
{
    return $this->ViewClassType;
}
public function setviewClassType(string $ViewClassType): self
{
    $this->ViewClassType = $ViewClassType;

    return $this;
}


  
    // TCMSFieldLookupMultiselectCheckboxesSelectFieldsFromTable
/**
* @return Collection<int, cmsFieldConf>
*/
public function getCmsFieldConfCollection(): Collection
{
    return $this->CmsFieldConfCollection;
}

public function addCmsFieldConfCollection(cmsFieldConf $CmsFieldConfMlt): self
{
    if (!$this->CmsFieldConfCollection->contains($CmsFieldConfMlt)) {
        $this->CmsFieldConfCollection->add($CmsFieldConfMlt);
        $CmsFieldConfMlt->set($this);
    }

    return $this;
}

public function removeCmsFieldConfCollection(cmsFieldConf $CmsFieldConfMlt): self
{
    if ($this->CmsFieldConfCollection->removeElement($CmsFieldConfMlt)) {
        // set the owning side to null (unless already changed)
        if ($CmsFieldConfMlt->get() === $this) {
            $CmsFieldConfMlt->set(null);
        }
    }

    return $this;
}


  
}
