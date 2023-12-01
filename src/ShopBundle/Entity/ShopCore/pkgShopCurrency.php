<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopCore;


class pkgShopCurrency {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldVarchar
/** @var string - Name */
private string $Name = '', 
    // TCMSFieldVarchar
/** @var string - Symbol */
private string $Symbol = '', 
    // TCMSFieldDecimal
/** @var float - Conversion factor */
private float $Factor = 1, 
    // TCMSFieldUniqueMarker
/** @var bool - Is the base currency */
private bool $IsBaseCurrency = false, 
    // TCMSFieldVarchar
/** @var string - ISO-4217 Code */
private string $Iso4217 = ''  ) {}

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


  
    // TCMSFieldUniqueMarker
public function isisBaseCurrency(): bool
{
    return $this->IsBaseCurrency;
}
public function setisBaseCurrency(bool $IsBaseCurrency): self
{
    $this->IsBaseCurrency = $IsBaseCurrency;

    return $this;
}


  
    // TCMSFieldVarchar
public function getiso4217(): string
{
    return $this->Iso4217;
}
public function setiso4217(string $Iso4217): self
{
    $this->Iso4217 = $Iso4217;

    return $this;
}


  
}
