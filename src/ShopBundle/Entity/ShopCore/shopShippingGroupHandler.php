<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopCore;


class shopShippingGroupHandler {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldVarchar
/** @var string - Name */
private string $Name = '', 
    // TCMSFieldVarchar
/** @var string - Class name */
private string $Class = '', 
    // TCMSFieldOption
/** @var string - Class type */
private string $ClassType = 'Core', 
    // TCMSFieldVarchar
/** @var string - Class subtype */
private string $ClassSubtype = ''  ) {}

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
public function getclass(): string
{
    return $this->Class;
}
public function setclass(string $Class): self
{
    $this->Class = $Class;

    return $this;
}


  
    // TCMSFieldOption
public function getclassType(): string
{
    return $this->ClassType;
}
public function setclassType(string $ClassType): self
{
    $this->ClassType = $ClassType;

    return $this;
}


  
    // TCMSFieldVarchar
public function getclassSubtype(): string
{
    return $this->ClassSubtype;
}
public function setclassSubtype(string $ClassSubtype): self
{
    $this->ClassSubtype = $ClassSubtype;

    return $this;
}


  
}
