<?php
namespace ChameleonSystem\SearchBundle\Entity;

use ChameleonSystem\ShopBundle\Entity\ShopCore\shop;
use ChameleonSystem\DataAccessBundle\Entity\Core\cmsLanguage;

class shopSearchIgnoreWord {
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
/** @var string - Word */
private string $Name = ''  ) {}

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


  
}
