<?php
namespace ChameleonSystem\ShopPaymentIPNBundle\Entity;

use ChameleonSystem\ShopPaymentIPNBundle\Entity\pkgShopPaymentIpnTrigger;
use ChameleonSystem\ShopPaymentIPNBundle\Entity\pkgShopPaymentIpnMessage;

class pkgShopPaymentIpnMessageTrigger {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var pkgShopPaymentIpnTrigger|null - Trigger */
private ?pkgShopPaymentIpnTrigger $PkgShopPaymentIpnTrigger = null
, 
    // TCMSFieldLookupParentID
/** @var pkgShopPaymentIpnMessage|null - IPN Message */
private ?pkgShopPaymentIpnMessage $PkgShopPaymentIpnMessage = null
, 
    // TCMSFieldCreatedTimestamp
/** @var \DateTime|null - Created on */
private ?\DateTime $Datecreated = null, 
    // TCMSFieldBoolean
/** @var bool - Processed */
private bool $Done = false, 
    // TCMSFieldDateTime
/** @var \DateTime|null - Processed on */
private ?\DateTime $DoneDate = null, 
    // TCMSFieldBoolean
/** @var bool - Successful */
private bool $Success = false, 
    // TCMSFieldNumber
/** @var int - Number of attempts */
private int $AttemptCount = 0, 
    // TCMSFieldDateTime
/** @var \DateTime|null - Next attempt on */
private ?\DateTime $NextAttempt = null, 
    // TCMSFieldText
/** @var string - Log */
private string $Log = ''  ) {}

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
public function getPkgShopPaymentIpnTrigger(): ?pkgShopPaymentIpnTrigger
{
    return $this->PkgShopPaymentIpnTrigger;
}

public function setPkgShopPaymentIpnTrigger(?pkgShopPaymentIpnTrigger $PkgShopPaymentIpnTrigger): self
{
    $this->PkgShopPaymentIpnTrigger = $PkgShopPaymentIpnTrigger;

    return $this;
}


  
    // TCMSFieldLookupParentID
public function getPkgShopPaymentIpnMessage(): ?pkgShopPaymentIpnMessage
{
    return $this->PkgShopPaymentIpnMessage;
}

public function setPkgShopPaymentIpnMessage(?pkgShopPaymentIpnMessage $PkgShopPaymentIpnMessage): self
{
    $this->PkgShopPaymentIpnMessage = $PkgShopPaymentIpnMessage;

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


  
    // TCMSFieldBoolean
public function isdone(): bool
{
    return $this->Done;
}
public function setdone(bool $Done): self
{
    $this->Done = $Done;

    return $this;
}


  
    // TCMSFieldDateTime
public function getdoneDate(): ?\DateTime
{
    return $this->DoneDate;
}
public function setdoneDate(?\DateTime $DoneDate): self
{
    $this->DoneDate = $DoneDate;

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


  
    // TCMSFieldNumber
public function getattemptCount(): int
{
    return $this->AttemptCount;
}
public function setattemptCount(int $AttemptCount): self
{
    $this->AttemptCount = $AttemptCount;

    return $this;
}


  
    // TCMSFieldDateTime
public function getnextAttempt(): ?\DateTime
{
    return $this->NextAttempt;
}
public function setnextAttempt(?\DateTime $NextAttempt): self
{
    $this->NextAttempt = $NextAttempt;

    return $this;
}


  
    // TCMSFieldText
public function getlog(): string
{
    return $this->Log;
}
public function setlog(string $Log): self
{
    $this->Log = $Log;

    return $this;
}


  
}
