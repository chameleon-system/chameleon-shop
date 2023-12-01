<?php
namespace ChameleonSystem\ShopRatingServiceBundle\Entity;

use ChameleonSystem\DataAccessBundle\Entity\CorePagedef\cmsTplModuleInstance;
use ChameleonSystem\ShopRatingServiceBundle\Entity\pkgShopRatingService;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class pkgShopRatingServiceTeaserCnf {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var cmsTplModuleInstance|null - Module instance */
private ?cmsTplModuleInstance $CmsTplModuleInstance = null
, 
    // TCMSFieldNumber
/** @var int - Number of ratings to be selected */
private int $NumberOfRatingsToSelectFrom = 0, 
    // TCMSFieldVarchar
/** @var string - Headline */
private string $Headline = '', 
    // TCMSFieldVarchar
/** @var string - Link name for "show all" */
private string $ShowAllLinkName = '', 
    // TCMSFieldLookupMultiselectCheckboxes
/** @var Collection<int, pkgShopRatingService> - Rating service */
private Collection $PkgShopRatingServiceCollection = new ArrayCollection()
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


  
    // TCMSFieldNumber
public function getnumberOfRatingsToSelectFrom(): int
{
    return $this->NumberOfRatingsToSelectFrom;
}
public function setnumberOfRatingsToSelectFrom(int $NumberOfRatingsToSelectFrom): self
{
    $this->NumberOfRatingsToSelectFrom = $NumberOfRatingsToSelectFrom;

    return $this;
}


  
    // TCMSFieldVarchar
public function getheadline(): string
{
    return $this->Headline;
}
public function setheadline(string $Headline): self
{
    $this->Headline = $Headline;

    return $this;
}


  
    // TCMSFieldVarchar
public function getshowAllLinkName(): string
{
    return $this->ShowAllLinkName;
}
public function setshowAllLinkName(string $ShowAllLinkName): self
{
    $this->ShowAllLinkName = $ShowAllLinkName;

    return $this;
}


  
    // TCMSFieldLookupMultiselectCheckboxes
/**
* @return Collection<int, pkgShopRatingService>
*/
public function getPkgShopRatingServiceCollection(): Collection
{
    return $this->PkgShopRatingServiceCollection;
}

public function addPkgShopRatingServiceCollection(pkgShopRatingService $PkgShopRatingServiceMlt): self
{
    if (!$this->PkgShopRatingServiceCollection->contains($PkgShopRatingServiceMlt)) {
        $this->PkgShopRatingServiceCollection->add($PkgShopRatingServiceMlt);
        $PkgShopRatingServiceMlt->set($this);
    }

    return $this;
}

public function removePkgShopRatingServiceCollection(pkgShopRatingService $PkgShopRatingServiceMlt): self
{
    if ($this->PkgShopRatingServiceCollection->removeElement($PkgShopRatingServiceMlt)) {
        // set the owning side to null (unless already changed)
        if ($PkgShopRatingServiceMlt->get() === $this) {
            $PkgShopRatingServiceMlt->set(null);
        }
    }

    return $this;
}


  
}
