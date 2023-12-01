<?php
namespace ChameleonSystem\ShopAffiliateBundle\Entity;

use ChameleonSystem\ShopBundle\Entity\ShopCore\shop;
use ChameleonSystem\ShopAffiliateBundle\Entity\pkgShopAffiliateParameter;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class pkgShopAffiliate {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shop|null - Belongs to shop */
private ?shop $Shop = null
, 
    // TCMSFieldVarchar
/** @var string - Name */
private string $Name = '', 
    // TCMSFieldVarchar
/** @var string - URL parameter used to transfer the tracking code */
private string $UrlParameterName = '', 
    // TCMSFieldNumber
/** @var int - Seconds, for which the code is still valid with inactive session */
private int $NumberOfSecondsValid = 0, 
    // TCMSFieldVarchar
/** @var string - Class */
private string $Class = '', 
    // TCMSFieldVarchar
/** @var string - Class subtype (path relative to ./classes) */
private string $ClassSubtype = '', 
    // TCMSFieldOption
/** @var string - Class type */
private string $ClassType = 'Customer', 
    // TCMSFieldText
/** @var string - Code to be integrated on order success page */
private string $OrderSuccessCode = '', 
    // TCMSFieldPropertyTable
/** @var Collection<int, pkgShopAffiliateParameter> - Parameter */
private Collection $PkgShopAffiliateParameterCollection = new ArrayCollection()
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
public function getShop(): ?shop
{
    return $this->Shop;
}

public function setShop(?shop $Shop): self
{
    $this->Shop = $Shop;

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
public function geturlParameterName(): string
{
    return $this->UrlParameterName;
}
public function seturlParameterName(string $UrlParameterName): self
{
    $this->UrlParameterName = $UrlParameterName;

    return $this;
}


  
    // TCMSFieldNumber
public function getnumberOfSecondsValid(): int
{
    return $this->NumberOfSecondsValid;
}
public function setnumberOfSecondsValid(int $NumberOfSecondsValid): self
{
    $this->NumberOfSecondsValid = $NumberOfSecondsValid;

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


  
    // TCMSFieldText
public function getorderSuccessCode(): string
{
    return $this->OrderSuccessCode;
}
public function setorderSuccessCode(string $OrderSuccessCode): self
{
    $this->OrderSuccessCode = $OrderSuccessCode;

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, pkgShopAffiliateParameter>
*/
public function getPkgShopAffiliateParameterCollection(): Collection
{
    return $this->PkgShopAffiliateParameterCollection;
}

public function addPkgShopAffiliateParameterCollection(pkgShopAffiliateParameter $PkgShopAffiliateParameter): self
{
    if (!$this->PkgShopAffiliateParameterCollection->contains($PkgShopAffiliateParameter)) {
        $this->PkgShopAffiliateParameterCollection->add($PkgShopAffiliateParameter);
        $PkgShopAffiliateParameter->setPkgShopAffiliate($this);
    }

    return $this;
}

public function removePkgShopAffiliateParameterCollection(pkgShopAffiliateParameter $PkgShopAffiliateParameter): self
{
    if ($this->PkgShopAffiliateParameterCollection->removeElement($PkgShopAffiliateParameter)) {
        // set the owning side to null (unless already changed)
        if ($PkgShopAffiliateParameter->getPkgShopAffiliate() === $this) {
            $PkgShopAffiliateParameter->setPkgShopAffiliate(null);
        }
    }

    return $this;
}


  
}
