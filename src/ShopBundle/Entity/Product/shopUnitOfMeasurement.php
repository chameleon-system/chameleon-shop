<?php
namespace ChameleonSystem\ShopBundle\Entity\Product;


class shopUnitOfMeasurement {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldVarchar
/** @var string - Name */
private string $Name = '', 
    // TCMSFieldVarchar
/** @var string - Symbol / abbreviation */
private string $Symbol = '', 
    // TCMSFieldDecimal
/** @var float - Factor */
private float $Factor = 0, 
    // TCMSFieldLookup
/** @var shopUnitOfMeasurement|null - Base unit */
private ?shopUnitOfMeasurement $ShopUnitOfMeasurement = null
  ) {}

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


  
    // TCMSFieldVarchar
public function getsymbol(): string
{
    return $this->Symbol;
}
public function setsymbol(string $Symbol): self
{
    $this->Symbol = $Symbol;

    return $this;
}


  
    // TCMSFieldDecimal
public function getfactor(): float
{
    return $this->Factor;
}
public function setfactor(float $Factor): self
{
    $this->Factor = $Factor;

    return $this;
}


  
    // TCMSFieldLookup
public function getShopUnitOfMeasurement(): ?shopUnitOfMeasurement
{
    return $this->ShopUnitOfMeasurement;
}

public function setShopUnitOfMeasurement(?shopUnitOfMeasurement $ShopUnitOfMeasurement): self
{
    $this->ShopUnitOfMeasurement = $ShopUnitOfMeasurement;

    return $this;
}


  
}
