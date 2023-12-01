<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopCore;


class shopVat {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldVarchar
/** @var string - Name */
private string $Name = '', 
    // TCMSFieldDecimal
/** @var float - Percentage */
private float $VatPercent = 0  ) {}

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


  
    // TCMSFieldDecimal
public function getvatPercent(): float
{
    return $this->VatPercent;
}
public function setvatPercent(float $VatPercent): self
{
    $this->VatPercent = $VatPercent;

    return $this;
}


  
}
