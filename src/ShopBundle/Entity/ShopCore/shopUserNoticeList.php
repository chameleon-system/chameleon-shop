<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

use ChameleonSystem\ExtranetBundle\Entity\dataExtranetUser;
use ChameleonSystem\ShopBundle\Entity\Product\shopArticle;

class shopUserNoticeList {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var dataExtranetUser|null - Belongs to customer */
private ?dataExtranetUser $DataExtranetUser = null
, 
    // TCMSFieldDateTime
/** @var \DateTime|null - Added */
private ?\DateTime $DateAdded = null, 
    // TCMSFieldExtendedLookup
/** @var shopArticle|null - Article */
private ?shopArticle $ShopArticle = null
, 
    // TCMSFieldDecimal
/** @var float - Units */
private float $Amount = 1  ) {}

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
public function getDataExtranetUser(): ?dataExtranetUser
{
    return $this->DataExtranetUser;
}

public function setDataExtranetUser(?dataExtranetUser $DataExtranetUser): self
{
    $this->DataExtranetUser = $DataExtranetUser;

    return $this;
}


  
    // TCMSFieldDateTime
public function getdateAdded(): ?\DateTime
{
    return $this->DateAdded;
}
public function setdateAdded(?\DateTime $DateAdded): self
{
    $this->DateAdded = $DateAdded;

    return $this;
}


  
    // TCMSFieldExtendedLookup
public function getShopArticle(): ?shopArticle
{
    return $this->ShopArticle;
}

public function setShopArticle(?shopArticle $ShopArticle): self
{
    $this->ShopArticle = $ShopArticle;

    return $this;
}


  
    // TCMSFieldDecimal
public function getamount(): float
{
    return $this->Amount;
}
public function setamount(float $Amount): self
{
    $this->Amount = $Amount;

    return $this;
}


  
}
