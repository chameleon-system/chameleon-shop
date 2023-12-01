<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

use ChameleonSystem\ShopBundle\Entity\ShopCore\shop;
use ChameleonSystem\DataAccessBundle\Entity\CorePagedef\cmsTree;

class shopSystemPage {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shop|null - Belongs to shop */
private ?shop $Shop = null
, 
    // TCMSFieldVarchar
/** @var string - Internal system name */
private string $NameInternal = '', 
    // TCMSFieldVarchar
/** @var string - Display name */
private string $Name = '', 
    // TCMSFieldTreeNode
/** @var cmsTree|null - Navigation item (node) */
private ?cmsTree $CmsTree = null
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
public function getnameInternal(): string
{
    return $this->NameInternal;
}
public function setnameInternal(string $NameInternal): self
{
    $this->NameInternal = $NameInternal;

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


  
    // TCMSFieldTreeNode
public function getCmsTree(): ?cmsTree
{
    return $this->CmsTree;
}

public function setCmsTree(?cmsTree $CmsTree): self
{
    $this->CmsTree = $CmsTree;

    return $this;
}


  
}
