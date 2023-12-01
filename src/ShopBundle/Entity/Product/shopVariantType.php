<?php
namespace ChameleonSystem\ShopBundle\Entity\Product;

use ChameleonSystem\ShopBundle\Entity\Product\shopVariantSet;
use ChameleonSystem\DataAccessBundle\Entity\CoreMedia\cmsMedia;
use ChameleonSystem\ShopBundle\Entity\Product\shopVariantTypeValue;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class shopVariantType {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shopVariantSet|null - Belongs to variant set */
private ?shopVariantSet $ShopVariantSet = null
, 
    // TCMSFieldSEOURLTitle
/** @var string - URL name */
private string $UrlName = '', 
    // TCMSFieldPosition
/** @var int - Sorting */
private int $Position = 0, 
    // TCMSFieldExtendedLookupMedia
/** @var cmsMedia|null - Image or icon for variant type (optional) */
private ?cmsMedia $CmsMedia = null
, 
    // TCMSFieldOption
/** @var string - Input type of variant values in the CMS */
private string $ValueSelectType = 'SelectBox', 
    // TCMSFieldTablefieldname
/** @var string - Order values by */
private string $ShopVariantTypeValueCmsfieldname = '', 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopVariantTypeValue> - Available variant values */
private Collection $ShopVariantTypeValueCollection = new ArrayCollection()
, 
    // TCMSFieldVarchar
/** @var string - Name */
private string $Name = '', 
    // TCMSFieldVarchar
/** @var string - Identifier */
private string $Identifier = ''  ) {}

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
public function getShopVariantSet(): ?shopVariantSet
{
    return $this->ShopVariantSet;
}

public function setShopVariantSet(?shopVariantSet $ShopVariantSet): self
{
    $this->ShopVariantSet = $ShopVariantSet;

    return $this;
}


  
    // TCMSFieldSEOURLTitle
public function geturlName(): string
{
    return $this->UrlName;
}
public function seturlName(string $UrlName): self
{
    $this->UrlName = $UrlName;

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


  
    // TCMSFieldExtendedLookupMedia
public function getCmsMedia(): ?cmsMedia
{
    return $this->CmsMedia;
}

public function setCmsMedia(?cmsMedia $CmsMedia): self
{
    $this->CmsMedia = $CmsMedia;

    return $this;
}


  
    // TCMSFieldOption
public function getvalueSelectType(): string
{
    return $this->ValueSelectType;
}
public function setvalueSelectType(string $ValueSelectType): self
{
    $this->ValueSelectType = $ValueSelectType;

    return $this;
}


  
    // TCMSFieldTablefieldname
public function getshopVariantTypeValueCmsfieldname(): string
{
    return $this->ShopVariantTypeValueCmsfieldname;
}
public function setshopVariantTypeValueCmsfieldname(string $ShopVariantTypeValueCmsfieldname): self
{
    $this->ShopVariantTypeValueCmsfieldname = $ShopVariantTypeValueCmsfieldname;

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopVariantTypeValue>
*/
public function getShopVariantTypeValueCollection(): Collection
{
    return $this->ShopVariantTypeValueCollection;
}

public function addShopVariantTypeValueCollection(shopVariantTypeValue $ShopVariantTypeValue): self
{
    if (!$this->ShopVariantTypeValueCollection->contains($ShopVariantTypeValue)) {
        $this->ShopVariantTypeValueCollection->add($ShopVariantTypeValue);
        $ShopVariantTypeValue->setShopVariantType($this);
    }

    return $this;
}

public function removeShopVariantTypeValueCollection(shopVariantTypeValue $ShopVariantTypeValue): self
{
    if ($this->ShopVariantTypeValueCollection->removeElement($ShopVariantTypeValue)) {
        // set the owning side to null (unless already changed)
        if ($ShopVariantTypeValue->getShopVariantType() === $this) {
            $ShopVariantTypeValue->setShopVariantType(null);
        }
    }

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
public function getidentifier(): string
{
    return $this->Identifier;
}
public function setidentifier(string $Identifier): self
{
    $this->Identifier = $Identifier;

    return $this;
}


  
}
