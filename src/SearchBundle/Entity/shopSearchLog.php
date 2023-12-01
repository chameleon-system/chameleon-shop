<?php
namespace ChameleonSystem\SearchBundle\Entity;

use ChameleonSystem\ShopBundle\Entity\ShopCore\shop;
use ChameleonSystem\DataAccessBundle\Entity\Core\cmsLanguage;
use ChameleonSystem\ExtranetBundle\Entity\dataExtranetUser;

class shopSearchLog {
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
/** @var string - Search term */
private string $Name = '', 
    // TCMSFieldNumber
/** @var int - Number of results */
private int $NumberOfResults = 0, 
    // TCMSFieldDateTime
/** @var \DateTime|null - Search date */
private ?\DateTime $SearchDate = null, 
    // TCMSFieldLookupParentID
/** @var dataExtranetUser|null - Executed by */
private ?dataExtranetUser $DataExtranetUser = null
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


  
    // TCMSFieldNumber
public function getnumberOfResults(): int
{
    return $this->NumberOfResults;
}
public function setnumberOfResults(int $NumberOfResults): self
{
    $this->NumberOfResults = $NumberOfResults;

    return $this;
}


  
    // TCMSFieldDateTime
public function getsearchDate(): ?\DateTime
{
    return $this->SearchDate;
}
public function setsearchDate(?\DateTime $SearchDate): self
{
    $this->SearchDate = $SearchDate;

    return $this;
}


  
    // TCMSFieldLookupParentID
public function getDataExtranetUser(): ?dataExtranetUser
{
    return $this->DataExtranetUser;
}

public function setDataExtranetUser(?dataExtranetUser $DataExtranetUser): self
{
    $this->DataExtranetUser = $DataExtranetUser;

    return $this;
}


  
}
