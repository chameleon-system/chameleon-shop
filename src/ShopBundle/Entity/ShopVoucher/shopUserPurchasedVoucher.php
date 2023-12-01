<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopVoucher;

use ChameleonSystem\ExtranetBundle\Entity\dataExtranetUser;
use ChameleonSystem\ShopBundle\Entity\ShopVoucher\shopVoucher;

class shopUserPurchasedVoucher {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var dataExtranetUser|null - Belongs to customer */
private ?dataExtranetUser $DataExtranetUser = null
, 
    // TCMSFieldExtendedLookup
/** @var shopVoucher|null - Voucher */
private ?shopVoucher $ShopVoucher = null
, 
    // TCMSFieldDateTime
/** @var \DateTime|null - Bought on */
private ?\DateTime $DatePurchased = null  ) {}

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


  
    // TCMSFieldExtendedLookup
public function getShopVoucher(): ?shopVoucher
{
    return $this->ShopVoucher;
}

public function setShopVoucher(?shopVoucher $ShopVoucher): self
{
    $this->ShopVoucher = $ShopVoucher;

    return $this;
}


  
    // TCMSFieldDateTime
public function getdatePurchased(): ?\DateTime
{
    return $this->DatePurchased;
}
public function setdatePurchased(?\DateTime $DatePurchased): self
{
    $this->DatePurchased = $DatePurchased;

    return $this;
}


  
}
