<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopOrder;

use ChameleonSystem\ShopBundle\Entity\ShopOrder\shopOrder;

class shopOrderBasket {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldVarcharUnique
/** @var string - Basket cart ID (will also be included in the order) */
private string $OrderIdent = '', 
    // TCMSFieldVarchar
/** @var string - Session ID */
private string $SessionId = '', 
    // TCMSFieldNumber
/** @var int - Created on */
private int $Datecreated = 0, 
    // TCMSFieldNumber
/** @var int - Last changed */
private int $Lastmodified = 0, 
    // TCMSFieldText
/** @var string - Basket */
private string $RawdataBasket = '', 
    // TCMSFieldText
/** @var string - User data */
private string $RawdataUser = '', 
    // TCMSFieldText
/** @var string - Session */
private string $RawdataSession = '', 
    // TCMSFieldLookup
/** @var shopOrder|null - Order */
private ?shopOrder $ShopOrder = null
, 
    // TCMSFieldVarchar
/** @var string - Last update in step */
private string $UpdateStepname = '', 
    // TCMSFieldBoolean
/** @var bool - Processed */
private bool $Processed = false  ) {}

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
    // TCMSFieldVarcharUnique
public function getorderIdent(): string
{
    return $this->OrderIdent;
}
public function setorderIdent(string $OrderIdent): self
{
    $this->OrderIdent = $OrderIdent;

    return $this;
}


  
    // TCMSFieldVarchar
public function getsessionId(): string
{
    return $this->SessionId;
}
public function setsessionId(string $SessionId): self
{
    $this->SessionId = $SessionId;

    return $this;
}


  
    // TCMSFieldNumber
public function getdatecreated(): int
{
    return $this->Datecreated;
}
public function setdatecreated(int $Datecreated): self
{
    $this->Datecreated = $Datecreated;

    return $this;
}


  
    // TCMSFieldNumber
public function getlastmodified(): int
{
    return $this->Lastmodified;
}
public function setlastmodified(int $Lastmodified): self
{
    $this->Lastmodified = $Lastmodified;

    return $this;
}


  
    // TCMSFieldText
public function getrawdataBasket(): string
{
    return $this->RawdataBasket;
}
public function setrawdataBasket(string $RawdataBasket): self
{
    $this->RawdataBasket = $RawdataBasket;

    return $this;
}


  
    // TCMSFieldText
public function getrawdataUser(): string
{
    return $this->RawdataUser;
}
public function setrawdataUser(string $RawdataUser): self
{
    $this->RawdataUser = $RawdataUser;

    return $this;
}


  
    // TCMSFieldText
public function getrawdataSession(): string
{
    return $this->RawdataSession;
}
public function setrawdataSession(string $RawdataSession): self
{
    $this->RawdataSession = $RawdataSession;

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


  
    // TCMSFieldVarchar
public function getupdateStepname(): string
{
    return $this->UpdateStepname;
}
public function setupdateStepname(string $UpdateStepname): self
{
    $this->UpdateStepname = $UpdateStepname;

    return $this;
}


  
    // TCMSFieldBoolean
public function isprocessed(): bool
{
    return $this->Processed;
}
public function setprocessed(bool $Processed): self
{
    $this->Processed = $Processed;

    return $this;
}


  
}
