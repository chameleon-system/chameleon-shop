<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopOrder;

use ChameleonSystem\DataAccessBundle\Entity\CorePagedef\cmsTree;

class shopOrderStep {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldVarchar
/** @var string - Internal name */
private string $Systemname = '', 
    // TCMSFieldSEOURLTitle
/** @var string - URL name */
private string $UrlName = '', 
    // TCMSFieldVarchar
/** @var string - Headline */
private string $Name = '', 
    // TCMSFieldBoolean
/** @var bool - Show in navigation list */
private bool $ShowInNavigation = true, 
    // TCMSFieldWYSIWYG
/** @var string - Description */
private string $Description = '', 
    // TCMSFieldPosition
/** @var int - Position */
private int $Position = 0, 
    // TCMSFieldVarchar
/** @var string - Class name */
private string $Class = '', 
    // TCMSFieldOption
/** @var string - Class type */
private string $ClassType = 'Core', 
    // TCMSFieldVarchar
/** @var string - Class subtype */
private string $ClassSubtype = 'pkgShop/objects/db/TShopOrderStep', 
    // TCMSFieldVarchar
/** @var string - View to use for the step */
private string $RenderViewName = '', 
    // TCMSFieldOption
/** @var string - View type */
private string $RenderViewType = 'Core', 
    // TCMSFieldVarchar
/** @var string - CSS icon class inactive */
private string $CssIconClassInactive = '', 
    // TCMSFieldVarchar
/** @var string - CSS icon class active */
private string $CssIconClassActive = '', 
    // TCMSFieldTreeNode
/** @var cmsTree|null - Use template */
private ?cmsTree $TemplateNodeCmsTree = null
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
public function getsystemname(): string
{
    return $this->Systemname;
}
public function setsystemname(string $Systemname): self
{
    $this->Systemname = $Systemname;

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
public function isshowInNavigation(): bool
{
    return $this->ShowInNavigation;
}
public function setshowInNavigation(bool $ShowInNavigation): self
{
    $this->ShowInNavigation = $ShowInNavigation;

    return $this;
}


  
    // TCMSFieldWYSIWYG
public function getdescription(): string
{
    return $this->Description;
}
public function setdescription(string $Description): self
{
    $this->Description = $Description;

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
public function getclass(): string
{
    return $this->Class;
}
public function setclass(string $Class): self
{
    $this->Class = $Class;

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
public function getclassSubtype(): string
{
    return $this->ClassSubtype;
}
public function setclassSubtype(string $ClassSubtype): self
{
    $this->ClassSubtype = $ClassSubtype;

    return $this;
}


  
    // TCMSFieldVarchar
public function getrenderViewName(): string
{
    return $this->RenderViewName;
}
public function setrenderViewName(string $RenderViewName): self
{
    $this->RenderViewName = $RenderViewName;

    return $this;
}


  
    // TCMSFieldOption
public function getrenderViewType(): string
{
    return $this->RenderViewType;
}
public function setrenderViewType(string $RenderViewType): self
{
    $this->RenderViewType = $RenderViewType;

    return $this;
}


  
    // TCMSFieldVarchar
public function getcssIconClassInactive(): string
{
    return $this->CssIconClassInactive;
}
public function setcssIconClassInactive(string $CssIconClassInactive): self
{
    $this->CssIconClassInactive = $CssIconClassInactive;

    return $this;
}


  
    // TCMSFieldVarchar
public function getcssIconClassActive(): string
{
    return $this->CssIconClassActive;
}
public function setcssIconClassActive(string $CssIconClassActive): self
{
    $this->CssIconClassActive = $CssIconClassActive;

    return $this;
}


  
    // TCMSFieldTreeNode
public function getTemplateNodeCmsTree(): ?cmsTree
{
    return $this->TemplateNodeCmsTree;
}

public function setTemplateNodeCmsTree(?cmsTree $TemplateNodeCmsTree): self
{
    $this->TemplateNodeCmsTree = $TemplateNodeCmsTree;

    return $this;
}


  
}
