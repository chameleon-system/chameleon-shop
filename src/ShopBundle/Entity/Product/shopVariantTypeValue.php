<?php
namespace ChameleonSystem\ShopBundle\Entity\Product;

use ChameleonSystem\ShopBundle\Entity\Product\shopVariantType;
use ChameleonSystem\DataAccessBundle\Entity\CoreMedia\cmsMedia;

class shopVariantTypeValue {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shopVariantType|null - Belongs to variant type */
private ?shopVariantType $ShopVariantType = null
, 
    // TCMSFieldVarchar
/** @var string - Name */
private string $Name = '', 
    // TCMSFieldSEOURLTitle
/** @var string - URL name (for article link) */
private string $UrlName = '', 
    // TCMSFieldPosition
/** @var int - Position */
private int $Position = 0, 
    // TCMSFieldColorpicker
/** @var string - Color value (optional) */
private string $ColorCode = '', 
    // TCMSFieldExtendedLookupMedia
/** @var cmsMedia|null - Optional image or icon */
private ?cmsMedia $CmsMedia = null
, 
    // TCMSFieldVarchar
/** @var string - Alternative name (grouping) */
private string $NameGrouped = '', 
    // TCMSFieldPrice
/** @var float - Surcharge / reduction */
private float $Surcharge = 0  ) {}

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
public function getShopVariantType(): ?shopVariantType
{
    return $this->ShopVariantType;
}

public function setShopVariantType(?shopVariantType $ShopVariantType): self
{
    $this->ShopVariantType = $ShopVariantType;

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


  
    // TCMSFieldColorpicker
public function getcolorCode(): string
{
    return $this->ColorCode;
}
public function setcolorCode(string $ColorCode): self
{
    $this->ColorCode = $ColorCode;

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


  
    // TCMSFieldVarchar
public function getnameGrouped(): string
{
    return $this->NameGrouped;
}
public function setnameGrouped(string $NameGrouped): self
{
    $this->NameGrouped = $NameGrouped;

    return $this;
}


  
    // TCMSFieldPrice
public function getsurcharge(): float
{
    return $this->Surcharge;
}
public function setsurcharge(float $Surcharge): self
{
    $this->Surcharge = $Surcharge;

    return $this;
}


  
}
