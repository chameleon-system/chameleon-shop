<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopOrder;

use ChameleonSystem\ShopBundle\Entity\ShopOrder\shopOrder;

class shopOrderExportLog {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shopOrder|null - Belongs to order */
private ?shopOrder $ShopOrder = null
, 
    // TCMSFieldDateTimeNow
/** @var \DateTime|null - Created on */
private ?\DateTime $Datecreated = new \DateTime(), 
    // TCMSFieldVarchar
/** @var string - IP */
private string $Ip = '', 
    // TCMSFieldText
/** @var string - Data */
private string $Data = '', 
    // TCMSFieldVarchar
/** @var string - Session ID */
private string $UserSessionId = ''  ) {}

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


  
    // TCMSFieldDateTimeNow
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


  
    // TCMSFieldText
public function getdata(): string
{
    return $this->Data;
}
public function setdata(string $Data): self
{
    $this->Data = $Data;

    return $this;
}


  
    // TCMSFieldVarchar
public function getuserSessionId(): string
{
    return $this->UserSessionId;
}
public function setuserSessionId(string $UserSessionId): self
{
    $this->UserSessionId = $UserSessionId;

    return $this;
}


  
}
