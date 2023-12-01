<?php
namespace ChameleonSystem\ShopAffiliateBundle\Entity;

use ChameleonSystem\ShopAffiliateBundle\Entity\pkgShopAffiliate;

class pkgShopAffiliateParameter {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var pkgShopAffiliate|null - Belongs to affiliate program */
private ?pkgShopAffiliate $PkgShopAffiliate = null
, 
    // TCMSFieldVarchar
/** @var string - Name */
private string $Name = '', 
    // TCMSFieldText
/** @var string - Value */
private string $Value = ''  ) {}

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
public function getPkgShopAffiliate(): ?pkgShopAffiliate
{
    return $this->PkgShopAffiliate;
}

public function setPkgShopAffiliate(?pkgShopAffiliate $PkgShopAffiliate): self
{
    $this->PkgShopAffiliate = $PkgShopAffiliate;

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


  
    // TCMSFieldText
public function getvalue(): string
{
    return $this->Value;
}
public function setvalue(string $Value): self
{
    $this->Value = $Value;

    return $this;
}


  
}
