<?php
namespace ChameleonSystem\SearchBundle\Entity;

use ChameleonSystem\ShopBundle\Entity\ShopCore\shop;
use ChameleonSystem\DataAccessBundle\Entity\Core\cmsLanguage;
use ChameleonSystem\SearchBundle\Entity\shopSearchQuery;

class shopSearchFieldWeight {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shop|null - Belongs to shop */
private ?shop $Shop = null
, 
    // TCMSFieldExtendedLookup
/** @var cmsLanguage|null - Language */
private ?cmsLanguage $CmsLanguage = null
, 
    // TCMSFieldVarchar
/** @var string - Descriptive name of the field / table combination */
private string $Name = '', 
    // TCMSFieldVarchar
/** @var string - Table */
private string $Tablename = '', 
    // TCMSFieldVarchar
/** @var string - Field */
private string $Fieldname = '', 
    // TCMSFieldDecimal
/** @var float - Weight */
private float $Weight = 0, 
    // TCMSFieldLookup
/** @var shopSearchQuery|null - Selection to be used */
private ?shopSearchQuery $ShopSearchQuery = null
, 
    // TCMSFieldVarchar
/** @var string - Field name in query */
private string $FieldNameInQuery = '', 
    // TCMSFieldBoolean
/** @var bool - Indexing partial words */
private bool $IndexPartialWords = true  ) {}

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


  
    // TCMSFieldExtendedLookup
public function getCmsLanguage(): ?cmsLanguage
{
    return $this->CmsLanguage;
}

public function setCmsLanguage(?cmsLanguage $CmsLanguage): self
{
    $this->CmsLanguage = $CmsLanguage;

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
public function gettablename(): string
{
    return $this->Tablename;
}
public function settablename(string $Tablename): self
{
    $this->Tablename = $Tablename;

    return $this;
}


  
    // TCMSFieldVarchar
public function getfieldname(): string
{
    return $this->Fieldname;
}
public function setfieldname(string $Fieldname): self
{
    $this->Fieldname = $Fieldname;

    return $this;
}


  
    // TCMSFieldDecimal
public function getweight(): float
{
    return $this->Weight;
}
public function setweight(float $Weight): self
{
    $this->Weight = $Weight;

    return $this;
}


  
    // TCMSFieldLookup
public function getShopSearchQuery(): ?shopSearchQuery
{
    return $this->ShopSearchQuery;
}

public function setShopSearchQuery(?shopSearchQuery $ShopSearchQuery): self
{
    $this->ShopSearchQuery = $ShopSearchQuery;

    return $this;
}


  
    // TCMSFieldVarchar
public function getfieldNameInQuery(): string
{
    return $this->FieldNameInQuery;
}
public function setfieldNameInQuery(string $FieldNameInQuery): self
{
    $this->FieldNameInQuery = $FieldNameInQuery;

    return $this;
}


  
    // TCMSFieldBoolean
public function isindexPartialWords(): bool
{
    return $this->IndexPartialWords;
}
public function setindexPartialWords(bool $IndexPartialWords): self
{
    $this->IndexPartialWords = $IndexPartialWords;

    return $this;
}


  
}
