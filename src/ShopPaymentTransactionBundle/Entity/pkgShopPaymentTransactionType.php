<?php
namespace ChameleonSystem\ShopPaymentTransactionBundle\Entity;


class pkgShopPaymentTransactionType {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldVarchar
/** @var string - Name */
private string $Name = '', 
    // TCMSFieldVarcharUnique
/** @var string - System name */
private string $SystemName = ''  ) {}

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


  
    // TCMSFieldVarcharUnique
public function getsystemName(): string
{
    return $this->SystemName;
}
public function setsystemName(string $SystemName): self
{
    $this->SystemName = $SystemName;

    return $this;
}


  
}
