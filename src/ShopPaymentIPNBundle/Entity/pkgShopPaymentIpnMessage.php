<?php
namespace ChameleonSystem\ShopPaymentIPNBundle\Entity;

use ChameleonSystem\ShopPaymentIPNBundle\Entity\pkgShopPaymentIpnMessageTrigger;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use ChameleonSystem\DataAccessBundle\Entity\CorePortal\cmsPortal;
use ChameleonSystem\ShopBundle\Entity\ShopOrder\shopOrder;
use ChameleonSystem\ShopBundle\Entity\Payment\shopPaymentHandlerGroup;
use ChameleonSystem\ShopPaymentIPNBundle\Entity\pkgShopPaymentIpnStatus;

class pkgShopPaymentIpnMessage {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldPropertyTable
/** @var Collection<int, pkgShopPaymentIpnMessageTrigger> - Forwarding logs */
private Collection $PkgShopPaymentIpnMessageTriggerCollection = new ArrayCollection()
, 
    // TCMSFieldExtendedLookup
/** @var cmsPortal|null - Activated via this portal */
private ?cmsPortal $CmsPortal = null
, 
    // TCMSFieldLookupParentID
/** @var shopOrder|null - Belongs to order (ID) */
private ?shopOrder $ShopOrder = null
, 
    // TCMSFieldLookupParentID
/** @var shopPaymentHandlerGroup|null - Payment provider */
private ?shopPaymentHandlerGroup $ShopPaymentHandlerGroup = null
, 
    // TCMSFieldCreatedTimestamp
/** @var \DateTime|null - Date */
private ?\DateTime $Datecreated = null, 
    // TCMSFieldExtendedLookup
/** @var pkgShopPaymentIpnStatus|null - Status */
private ?pkgShopPaymentIpnStatus $PkgShopPaymentIpnStatus = null
, 
    // TCMSFieldBoolean
/** @var bool - Processed successfully */
private bool $Success = false, 
    // TCMSFieldBoolean
/** @var bool - Processed message */
private bool $Completed = false, 
    // TCMSFieldVarchar
/** @var string - Type of error */
private string $ErrorType = '', 
    // TCMSFieldVarchar
/** @var string - IP */
private string $Ip = '', 
    // TCMSFieldVarchar
/** @var string - Request URL */
private string $RequestUrl = '', 
    // TCMSFieldBlob
/** @var object|null - Payload */
private ?object $Payload = null, 
    // TCMSFieldText
/** @var string - Error details */
private string $Errors = ''  ) {}

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
    // TCMSFieldPropertyTable
/**
* @return Collection<int, pkgShopPaymentIpnMessageTrigger>
*/
public function getPkgShopPaymentIpnMessageTriggerCollection(): Collection
{
    return $this->PkgShopPaymentIpnMessageTriggerCollection;
}

public function addPkgShopPaymentIpnMessageTriggerCollection(pkgShopPaymentIpnMessageTrigger $PkgShopPaymentIpnMessageTrigger): self
{
    if (!$this->PkgShopPaymentIpnMessageTriggerCollection->contains($PkgShopPaymentIpnMessageTrigger)) {
        $this->PkgShopPaymentIpnMessageTriggerCollection->add($PkgShopPaymentIpnMessageTrigger);
        $PkgShopPaymentIpnMessageTrigger->setPkgShopPaymentIpnMessage($this);
    }

    return $this;
}

public function removePkgShopPaymentIpnMessageTriggerCollection(pkgShopPaymentIpnMessageTrigger $PkgShopPaymentIpnMessageTrigger): self
{
    if ($this->PkgShopPaymentIpnMessageTriggerCollection->removeElement($PkgShopPaymentIpnMessageTrigger)) {
        // set the owning side to null (unless already changed)
        if ($PkgShopPaymentIpnMessageTrigger->getPkgShopPaymentIpnMessage() === $this) {
            $PkgShopPaymentIpnMessageTrigger->setPkgShopPaymentIpnMessage(null);
        }
    }

    return $this;
}


  
    // TCMSFieldExtendedLookup
public function getCmsPortal(): ?cmsPortal
{
    return $this->CmsPortal;
}

public function setCmsPortal(?cmsPortal $CmsPortal): self
{
    $this->CmsPortal = $CmsPortal;

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


  
    // TCMSFieldLookupParentID
public function getShopPaymentHandlerGroup(): ?shopPaymentHandlerGroup
{
    return $this->ShopPaymentHandlerGroup;
}

public function setShopPaymentHandlerGroup(?shopPaymentHandlerGroup $ShopPaymentHandlerGroup): self
{
    $this->ShopPaymentHandlerGroup = $ShopPaymentHandlerGroup;

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


  
    // TCMSFieldExtendedLookup
public function getPkgShopPaymentIpnStatus(): ?pkgShopPaymentIpnStatus
{
    return $this->PkgShopPaymentIpnStatus;
}

public function setPkgShopPaymentIpnStatus(?pkgShopPaymentIpnStatus $PkgShopPaymentIpnStatus): self
{
    $this->PkgShopPaymentIpnStatus = $PkgShopPaymentIpnStatus;

    return $this;
}


  
    // TCMSFieldBoolean
public function issuccess(): bool
{
    return $this->Success;
}
public function setsuccess(bool $Success): self
{
    $this->Success = $Success;

    return $this;
}


  
    // TCMSFieldBoolean
public function iscompleted(): bool
{
    return $this->Completed;
}
public function setcompleted(bool $Completed): self
{
    $this->Completed = $Completed;

    return $this;
}


  
    // TCMSFieldVarchar
public function geterrorType(): string
{
    return $this->ErrorType;
}
public function seterrorType(string $ErrorType): self
{
    $this->ErrorType = $ErrorType;

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


  
    // TCMSFieldVarchar
public function getrequestUrl(): string
{
    return $this->RequestUrl;
}
public function setrequestUrl(string $RequestUrl): self
{
    $this->RequestUrl = $RequestUrl;

    return $this;
}


  
    // TCMSFieldBlob
public function getpayload(): ?object
{
    return $this->Payload;
}
public function setpayload(?object $Payload): self
{
    $this->Payload = $Payload;

    return $this;
}


  
    // TCMSFieldText
public function geterrors(): string
{
    return $this->Errors;
}
public function seterrors(string $Errors): self
{
    $this->Errors = $Errors;

    return $this;
}


  
}
