<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

use ChameleonSystem\ShopBundle\Entity\ShopCore\pkgShopListfilter;
use ChameleonSystem\ShopBundle\Entity\ShopCore\pkgShopListfilterItemType;
use ChameleonSystem\ShopBundle\Entity\Product\shopAttribute;

class pkgShopListfilterItem {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var pkgShopListfilter|null - Belongs to list filter configuration */
private ?pkgShopListfilter $PkgShopListfilter = null
, 
    // TCMSFieldLookup
/** @var pkgShopListfilterItemType|null - Filter type */
private ?pkgShopListfilterItemType $PkgShopListfilterItemT = null
, 
    // TCMSFieldVarchar
/** @var string - Name */
private string $Name = '', 
    // TCMSFieldVarchar
/** @var string - System name */
private string $Systemname = '', 
    // TCMSFieldExtendedLookup
/** @var shopAttribute|null - Belonging product attribute */
private ?shopAttribute $ShopAttrib = null
, 
    // TCMSFieldBoolean
/** @var bool - Multiple selections */
private bool $AllowMultiSelection = false, 
    // TCMSFieldBoolean
/** @var bool - Show all when opening the page? */
private bool $ShowAllOnPageLoad = true, 
    // TCMSFieldNumber
/** @var int - Window size */
private int $PreviewSize = 0, 
    // TCMSFieldBoolean
/** @var bool - Show scrollbars instead of "show all" button? */
private bool $ShowScrollbars = false, 
    // TCMSFieldNumber
/** @var int - Lowest value */
private int $MinValue = 0, 
    // TCMSFieldNumber
/** @var int - Highest value */
private int $MaxValue = 0, 
    // TCMSFieldVarchar
/** @var string - MySQL field name */
private string $MysqlFieldName = '', 
    // TCMSFieldVarchar
/** @var string - View */
private string $View = '', 
    // TCMSFieldOption
/** @var string - View class type */
private string $ViewClassType = 'Customer', 
    // TCMSFieldPosition
/** @var int - Sorting */
private int $Position = 0, 
    // TCMSFieldVarchar
/** @var string - System name of the variant type */
private string $VariantIdentifier = ''  ) {}

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
public function getPkgShopListfilter(): ?pkgShopListfilter
{
    return $this->PkgShopListfilter;
}

public function setPkgShopListfilter(?pkgShopListfilter $PkgShopListfilter): self
{
    $this->PkgShopListfilter = $PkgShopListfilter;

    return $this;
}


  
    // TCMSFieldLookup
public function getPkgShopListfilterItemT(): ?pkgShopListfilterItemType
{
    return $this->PkgShopListfilterItemT;
}

public function setPkgShopListfilterItemT(?pkgShopListfilterItemType $PkgShopListfilterItemT): self
{
    $this->PkgShopListfilterItemT = $PkgShopListfilterItemT;

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
public function getsystemname(): string
{
    return $this->Systemname;
}
public function setsystemname(string $Systemname): self
{
    $this->Systemname = $Systemname;

    return $this;
}


  
    // TCMSFieldExtendedLookup
public function getShopAttrib(): ?shopAttribute
{
    return $this->ShopAttrib;
}

public function setShopAttrib(?shopAttribute $ShopAttrib): self
{
    $this->ShopAttrib = $ShopAttrib;

    return $this;
}


  
    // TCMSFieldBoolean
public function isallowMultiSelection(): bool
{
    return $this->AllowMultiSelection;
}
public function setallowMultiSelection(bool $AllowMultiSelection): self
{
    $this->AllowMultiSelection = $AllowMultiSelection;

    return $this;
}


  
    // TCMSFieldBoolean
public function isshowAllOnPageLoad(): bool
{
    return $this->ShowAllOnPageLoad;
}
public function setshowAllOnPageLoad(bool $ShowAllOnPageLoad): self
{
    $this->ShowAllOnPageLoad = $ShowAllOnPageLoad;

    return $this;
}


  
    // TCMSFieldNumber
public function getpreviewSize(): int
{
    return $this->PreviewSize;
}
public function setpreviewSize(int $PreviewSize): self
{
    $this->PreviewSize = $PreviewSize;

    return $this;
}


  
    // TCMSFieldBoolean
public function isshowScrollbars(): bool
{
    return $this->ShowScrollbars;
}
public function setshowScrollbars(bool $ShowScrollbars): self
{
    $this->ShowScrollbars = $ShowScrollbars;

    return $this;
}


  
    // TCMSFieldNumber
public function getminValue(): int
{
    return $this->MinValue;
}
public function setminValue(int $MinValue): self
{
    $this->MinValue = $MinValue;

    return $this;
}


  
    // TCMSFieldNumber
public function getmaxValue(): int
{
    return $this->MaxValue;
}
public function setmaxValue(int $MaxValue): self
{
    $this->MaxValue = $MaxValue;

    return $this;
}


  
    // TCMSFieldVarchar
public function getmysqlFieldName(): string
{
    return $this->MysqlFieldName;
}
public function setmysqlFieldName(string $MysqlFieldName): self
{
    $this->MysqlFieldName = $MysqlFieldName;

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


  
    // TCMSFieldVarchar
public function getvariantIdentifier(): string
{
    return $this->VariantIdentifier;
}
public function setvariantIdentifier(string $VariantIdentifier): self
{
    $this->VariantIdentifier = $VariantIdentifier;

    return $this;
}


  
}
