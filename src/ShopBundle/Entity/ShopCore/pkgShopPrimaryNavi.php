<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

use ChameleonSystem\DataAccessBundle\Entity\CorePortal\cmsPortal;

class pkgShopPrimaryNavi {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var cmsPortal|null - Belongs to portal */
private ?cmsPortal $CmsPortal = null
, 
    // TCMSFieldVarchar
/** @var string - Name */
private string $Name = '', 
    // TCMSFieldBoolean
/** @var bool - Active */
private bool $Active = false, 
    // TCMSFieldPosition
/** @var int - Position */
private int $Position = 0, 
    // TCMSFieldExtendedLookupMultiTable
/** @var string - Select navigation */
private string $Target = '',
// TCMSFieldExtendedLookupMultiTable
/** @var string - Select navigation */
private string $TargetTableName = '', 
    // TCMSFieldBoolean
/** @var bool - Replace submenu with shop main categories */
private bool $ShowRootCategoryTree = false, 
    // TCMSFieldVarchar
/** @var string - Individual CSS class */
private string $CssClass = ''  ) {}

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
public function getCmsPortal(): ?cmsPortal
{
    return $this->CmsPortal;
}

public function setCmsPortal(?cmsPortal $CmsPortal): self
{
    $this->CmsPortal = $CmsPortal;

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


  
    // TCMSFieldBoolean
public function isactive(): bool
{
    return $this->Active;
}
public function setactive(bool $Active): self
{
    $this->Active = $Active;

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


  
    // TCMSFieldExtendedLookupMultiTable
public function gettarget(): string
{
    return $this->Target;
}
public function settarget(string $Target): self
{
    $this->Target = $Target;

    return $this;
}
// TCMSFieldExtendedLookupMultiTable
public function gettargetTableName(): string
{
    return $this->TargetTableName;
}
public function settargetTableName(string $TargetTableName): self
{
    $this->TargetTableName = $TargetTableName;

    return $this;
}


  
    // TCMSFieldBoolean
public function isshowRootCategoryTree(): bool
{
    return $this->ShowRootCategoryTree;
}
public function setshowRootCategoryTree(bool $ShowRootCategoryTree): self
{
    $this->ShowRootCategoryTree = $ShowRootCategoryTree;

    return $this;
}


  
    // TCMSFieldVarchar
public function getcssClass(): string
{
    return $this->CssClass;
}
public function setcssClass(string $CssClass): self
{
    $this->CssClass = $CssClass;

    return $this;
}


  
}
