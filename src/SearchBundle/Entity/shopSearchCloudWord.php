<?php
namespace ChameleonSystem\SearchBundle\Entity;


class shopSearchCloudWord {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldVarchar
/** @var string - Word */
private string $Name = '', 
    // TCMSFieldDecimal
/** @var float - Percentage weight relative to real search terms */
private float $Weight = 0  ) {}

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
public function getweight(): float
{
    return $this->Weight;
}
public function setweight(float $Weight): self
{
    $this->Weight = $Weight;

    return $this;
}


  
}
