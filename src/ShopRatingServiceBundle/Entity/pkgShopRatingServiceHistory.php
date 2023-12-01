<?php
namespace ChameleonSystem\ShopRatingServiceBundle\Entity;

use ChameleonSystem\ExtranetBundle\Entity\dataExtranetUser;
use ChameleonSystem\ShopBundle\Entity\ShopOrder\shopOrder;

class pkgShopRatingServiceHistory {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldExtendedLookup
/** @var dataExtranetUser|null - User */
private ?dataExtranetUser $DataExtranetUser = null
, 
    // TCMSFieldLookup
/** @var shopOrder|null - Belongs to order */
private ?shopOrder $ShopOrder = null
, 
    // TCMSFieldDateTime
/** @var \DateTime|null - Date */
private ?\DateTime $Date = null, 
    // TCMSFieldVarchar
/** @var string - List of rating services */
private string $PkgShopRatingServiceIdList = ''  ) {}

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
    // TCMSFieldExtendedLookup
public function getDataExtranetUser(): ?dataExtranetUser
{
    return $this->DataExtranetUser;
}

public function setDataExtranetUser(?dataExtranetUser $DataExtranetUser): self
{
    $this->DataExtranetUser = $DataExtranetUser;

    return $this;
}


  
    // TCMSFieldLookup
public function getShopOrder(): ?shopOrder
{
    return $this->ShopOrder;
}

public function setShopOrder(?shopOrder $ShopOrder): self
{
    $this->ShopOrder = $ShopOrder;

    return $this;
}


  
    // TCMSFieldDateTime
public function getdate(): ?\DateTime
{
    return $this->Date;
}
public function setdate(?\DateTime $Date): self
{
    $this->Date = $Date;

    return $this;
}


  
    // TCMSFieldVarchar
public function getpkgShopRatingServiceIdList(): string
{
    return $this->PkgShopRatingServiceIdList;
}
public function setpkgShopRatingServiceIdList(string $PkgShopRatingServiceIdList): self
{
    $this->PkgShopRatingServiceIdList = $PkgShopRatingServiceIdList;

    return $this;
}


  
}
