<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopOrder;

use ChameleonSystem\ShopBundle\Entity\ShopCore\shop;
use ChameleonSystem\ShopPaymentTransactionBundle\Entity\pkgShopPaymentTransactionType;
use ChameleonSystem\DataAccessBundle\Entity\Core\dataMailProfile;

class shopOrderStatusCode {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldVarchar
/** @var string - Name */
private string $Name = '', 
    // TCMSFieldLookupParentID
/** @var shop|null - Belongs to shop */
private ?shop $Shop = null
, 
    // TCMSFieldBoolean
/** @var bool - Send status notification via email */
private bool $SendMailNotification = true, 
    // TCMSFieldVarchar
/** @var string - System name / merchandise management code */
private string $SystemName = '', 
    // TCMSFieldExtendedLookup
/** @var pkgShopPaymentTransactionType|null - Run following transaction, if status is executed */
private ?pkgShopPaymentTransactionType $PkgShopPaymentTransactionType = null
, 
    // TCMSFieldLookup
/** @var dataMailProfile|null - Email profile */
private ?dataMailProfile $DataMailProfile = null
, 
    // TCMSFieldWYSIWYG
/** @var string - Status text */
private string $InfoText = ''  ) {}

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
public function getname(): string
{
    return $this->Name;
}
public function setname(string $Name): self
{
    $this->Name = $Name;

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


  
    // TCMSFieldBoolean
public function issendMailNotification(): bool
{
    return $this->SendMailNotification;
}
public function setsendMailNotification(bool $SendMailNotification): self
{
    $this->SendMailNotification = $SendMailNotification;

    return $this;
}


  
    // TCMSFieldVarchar
public function getsystemName(): string
{
    return $this->SystemName;
}
public function setsystemName(string $SystemName): self
{
    $this->SystemName = $SystemName;

    return $this;
}


  
    // TCMSFieldExtendedLookup
public function getPkgShopPaymentTransactionType(): ?pkgShopPaymentTransactionType
{
    return $this->PkgShopPaymentTransactionType;
}

public function setPkgShopPaymentTransactionType(?pkgShopPaymentTransactionType $PkgShopPaymentTransactionType): self
{
    $this->PkgShopPaymentTransactionType = $PkgShopPaymentTransactionType;

    return $this;
}


  
    // TCMSFieldLookup
public function getDataMailProfile(): ?dataMailProfile
{
    return $this->DataMailProfile;
}

public function setDataMailProfile(?dataMailProfile $DataMailProfile): self
{
    $this->DataMailProfile = $DataMailProfile;

    return $this;
}


  
    // TCMSFieldWYSIWYG
public function getinfoText(): string
{
    return $this->InfoText;
}
public function setinfoText(string $InfoText): self
{
    $this->InfoText = $InfoText;

    return $this;
}


  
}
