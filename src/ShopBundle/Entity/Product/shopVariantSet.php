<?php
namespace ChameleonSystem\ShopBundle\Entity\Product;

use ChameleonSystem\ShopBundle\Entity\Product\shopVariantType;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use ChameleonSystem\DataAccessBundle\Entity\CoreTableConfiguration\cmsFieldConf;
use ChameleonSystem\ShopBundle\Entity\Product\shopVariantDisplayHandler;

class shopVariantSet {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldVarchar
/** @var string - Name */
private string $Name = '', 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopVariantType> - Variant types of variant set */
private Collection $ShopVariantTypeCollection = new ArrayCollection()
, 
    // TCMSFieldLookupMultiselectCheckboxesSelectFieldsFromTable
/** @var Collection<int, cmsFieldConf> - Fields of variant which may differ from parent item */
private Collection $CmsFieldConfCollection = new ArrayCollection()
, 
    // TCMSFieldLookup
/** @var shopVariantDisplayHandler|null - Display handler for variant selection in  shop */
private ?shopVariantDisplayHandler $ShopVariantDisplayHandler = null
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


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopVariantType>
*/
public function getShopVariantTypeCollection(): Collection
{
    return $this->ShopVariantTypeCollection;
}

public function addShopVariantTypeCollection(shopVariantType $ShopVariantType): self
{
    if (!$this->ShopVariantTypeCollection->contains($ShopVariantType)) {
        $this->ShopVariantTypeCollection->add($ShopVariantType);
        $ShopVariantType->setShopVariantSet($this);
    }

    return $this;
}

public function removeShopVariantTypeCollection(shopVariantType $ShopVariantType): self
{
    if ($this->ShopVariantTypeCollection->removeElement($ShopVariantType)) {
        // set the owning side to null (unless already changed)
        if ($ShopVariantType->getShopVariantSet() === $this) {
            $ShopVariantType->setShopVariantSet(null);
        }
    }

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


  
    // TCMSFieldLookup
public function getShopVariantDisplayHandler(): ?shopVariantDisplayHandler
{
    return $this->ShopVariantDisplayHandler;
}

public function setShopVariantDisplayHandler(?shopVariantDisplayHandler $ShopVariantDisplayHandler): self
{
    $this->ShopVariantDisplayHandler = $ShopVariantDisplayHandler;

    return $this;
}


  
}
