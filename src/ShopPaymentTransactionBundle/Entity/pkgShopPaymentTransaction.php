<?php
namespace ChameleonSystem\ShopPaymentTransactionBundle\Entity;

use ChameleonSystem\ShopBundle\Entity\ShopOrder\shopOrder;
use ChameleonSystem\ExtranetBundle\Entity\dataExtranetUser;
use ChameleonSystem\ShopPaymentTransactionBundle\Entity\pkgShopPaymentTransactionPosition;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use ChameleonSystem\ShopPaymentTransactionBundle\Entity\pkgShopPaymentTransactionType;
use ChameleonSystem\DataAccessBundle\Entity\Core\cmsUser;

class pkgShopPaymentTransaction {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shopOrder|null - Belongs to order */
private ?shopOrder $ShopOrder = null
, 
    // TCMSFieldExtendedLookup
/** @var dataExtranetUser|null - Executed by user */
private ?dataExtranetUser $DataExtranetUser = null
, 
    // TCMSFieldPropertyTable
/** @var Collection<int, pkgShopPaymentTransactionPosition> - Positions */
private Collection $PkgShopPaymentTransactionPositionCollection = new ArrayCollection()
, 
    // TCMSFieldLookup
/** @var pkgShopPaymentTransactionType|null - Transaction type */
private ?pkgShopPaymentTransactionType $PkgShopPaymentTransactionType = null
, 
    // TCMSFieldExtendedLookup
/** @var cmsUser|null - Executed by CMS user */
private ?cmsUser $CmsUser = null
, 
    // TCMSFieldCreatedTimestamp
/** @var \DateTime|null - Created on */
private ?\DateTime $Datecreated = null, 
    // TCMSFieldVarchar
/** @var string - Executed via IP */
private string $Ip = '', 
    // TCMSFieldDecimal
/** @var float - Value */
private float $Amount = 0, 
    // TCMSFieldVarchar
/** @var string - Context */
private string $Context = '', 
    // TCMSFieldNumber
/** @var int - Sequence number */
private int $SequenceNumber = 0, 
    // TCMSFieldBoolean
/** @var bool - Confirmed */
private bool $Confirmed = false, 
    // TCMSFieldDateTime
/** @var \DateTime|null - Confirmed on */
private ?\DateTime $ConfirmedDate = null  ) {}

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
public function getShopOrder(): ?shopOrder
{
    return $this->ShopOrder;
}

public function setShopOrder(?shopOrder $ShopOrder): self
{
    $this->ShopOrder = $ShopOrder;

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


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, pkgShopPaymentTransactionPosition>
*/
public function getPkgShopPaymentTransactionPositionCollection(): Collection
{
    return $this->PkgShopPaymentTransactionPositionCollection;
}

public function addPkgShopPaymentTransactionPositionCollection(pkgShopPaymentTransactionPosition $PkgShopPaymentTransactionPosition): self
{
    if (!$this->PkgShopPaymentTransactionPositionCollection->contains($PkgShopPaymentTransactionPosition)) {
        $this->PkgShopPaymentTransactionPositionCollection->add($PkgShopPaymentTransactionPosition);
        $PkgShopPaymentTransactionPosition->setPkgShopPaymentTransaction($this);
    }

    return $this;
}

public function removePkgShopPaymentTransactionPositionCollection(pkgShopPaymentTransactionPosition $PkgShopPaymentTransactionPosition): self
{
    if ($this->PkgShopPaymentTransactionPositionCollection->removeElement($PkgShopPaymentTransactionPosition)) {
        // set the owning side to null (unless already changed)
        if ($PkgShopPaymentTransactionPosition->getPkgShopPaymentTransaction() === $this) {
            $PkgShopPaymentTransactionPosition->setPkgShopPaymentTransaction(null);
        }
    }

    return $this;
}


  
    // TCMSFieldLookup
public function getPkgShopPaymentTransactionType(): ?pkgShopPaymentTransactionType
{
    return $this->PkgShopPaymentTransactionType;
}

public function setPkgShopPaymentTransactionType(?pkgShopPaymentTransactionType $PkgShopPaymentTransactionType): self
{
    $this->PkgShopPaymentTransactionType = $PkgShopPaymentTransactionType;

    return $this;
}


  
    // TCMSFieldExtendedLookup
public function getCmsUser(): ?cmsUser
{
    return $this->CmsUser;
}

public function setCmsUser(?cmsUser $CmsUser): self
{
    $this->CmsUser = $CmsUser;

    return $this;
}


  
    // TCMSFieldCreatedTimestamp
public function getdatecreated(): ?\DateTime
{
    return $this->Datecreated;
}
public function setdatecreated(?\DateTime $Datecreated): self
{
    $this->Datecreated = $Datecreated;

    return $this;
}


  
    // TCMSFieldVarchar
public function getip(): string
{
    return $this->Ip;
}
public function setip(string $Ip): self
{
    $this->Ip = $Ip;

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


  
    // TCMSFieldVarchar
public function getcontext(): string
{
    return $this->Context;
}
public function setcontext(string $Context): self
{
    $this->Context = $Context;

    return $this;
}


  
    // TCMSFieldNumber
public function getsequenceNumber(): int
{
    return $this->SequenceNumber;
}
public function setsequenceNumber(int $SequenceNumber): self
{
    $this->SequenceNumber = $SequenceNumber;

    return $this;
}


  
    // TCMSFieldBoolean
public function isconfirmed(): bool
{
    return $this->Confirmed;
}
public function setconfirmed(bool $Confirmed): self
{
    $this->Confirmed = $Confirmed;

    return $this;
}


  
    // TCMSFieldDateTime
public function getconfirmedDate(): ?\DateTime
{
    return $this->ConfirmedDate;
}
public function setconfirmedDate(?\DateTime $ConfirmedDate): self
{
    $this->ConfirmedDate = $ConfirmedDate;

    return $this;
}


  
}
