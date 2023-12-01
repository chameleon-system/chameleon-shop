<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

use ChameleonSystem\ShopBundle\Entity\ShopCore\shop;

class shopBankAccount {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shop|null - Belongs to shop */
private ?shop $Shop = null
, 
    // TCMSFieldVarchar
/** @var string - Name */
private string $Name = '', 
    // TCMSFieldVarchar
/** @var string - Account owner */
private string $AccountOwner = '', 
    // TCMSFieldVarchar
/** @var string - Bank name */
private string $Bankname = '', 
    // TCMSFieldVarchar
/** @var string - Bank code */
private string $Bankcode = '', 
    // TCMSFieldVarchar
/** @var string - Account number */
private string $AccountNumber = '', 
    // TCMSFieldVarchar
/** @var string - BIC code */
private string $BicCode = '', 
    // TCMSFieldVarchar
/** @var string - IBAN number */
private string $Ibannumber = '', 
    // TCMSFieldPosition
/** @var int - Position */
private int $Position = 0  ) {}

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


  
    // TCMSFieldVarchar
public function getaccountOwner(): string
{
    return $this->AccountOwner;
}
public function setaccountOwner(string $AccountOwner): self
{
    $this->AccountOwner = $AccountOwner;

    return $this;
}


  
    // TCMSFieldVarchar
public function getbankname(): string
{
    return $this->Bankname;
}
public function setbankname(string $Bankname): self
{
    $this->Bankname = $Bankname;

    return $this;
}


  
    // TCMSFieldVarchar
public function getbankcode(): string
{
    return $this->Bankcode;
}
public function setbankcode(string $Bankcode): self
{
    $this->Bankcode = $Bankcode;

    return $this;
}


  
    // TCMSFieldVarchar
public function getaccountNumber(): string
{
    return $this->AccountNumber;
}
public function setaccountNumber(string $AccountNumber): self
{
    $this->AccountNumber = $AccountNumber;

    return $this;
}


  
    // TCMSFieldVarchar
public function getbicCode(): string
{
    return $this->BicCode;
}
public function setbicCode(string $BicCode): self
{
    $this->BicCode = $BicCode;

    return $this;
}


  
    // TCMSFieldVarchar
public function getibannumber(): string
{
    return $this->Ibannumber;
}
public function setibannumber(string $Ibannumber): self
{
    $this->Ibannumber = $Ibannumber;

    return $this;
}


  
    // TCMSFieldPosition
public function getposition(): int
{
    return $this->Position;
}
public function setposition(int $Position): self
{
    $this->Position = $Position;

    return $this;
}


  
}
