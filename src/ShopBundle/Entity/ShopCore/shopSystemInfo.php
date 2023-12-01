<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

use ChameleonSystem\ShopBundle\Entity\ShopCore\shop;

class shopSystemInfo {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shop|null - Belongs to shop */
private ?shop $Shop = null
, 
    // TCMSFieldVarchar
/** @var string - System name */
private string $NameInternal = '', 
    // TCMSFieldVarchar
/** @var string - Name */
private string $Name = '', 
    // TCMSFieldVarchar
/** @var string - Title */
private string $Titel = '', 
    // TCMSFieldWYSIWYG
/** @var string - Content */
private string $Content = ''  ) {}

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


  
    // TCMSFieldVarchar
public function gettitel(): string
{
    return $this->Titel;
}
public function settitel(string $Titel): self
{
    $this->Titel = $Titel;

    return $this;
}


  
    // TCMSFieldWYSIWYG
public function getcontent(): string
{
    return $this->Content;
}
public function setcontent(string $Content): self
{
    $this->Content = $Content;

    return $this;
}


  
}
