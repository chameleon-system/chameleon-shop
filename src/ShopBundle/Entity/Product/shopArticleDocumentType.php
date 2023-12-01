<?php
namespace ChameleonSystem\ShopBundle\Entity\Product;


class shopArticleDocumentType {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldVarchar
/** @var string - Title / Headline */
private string $Name = '', 
    // TCMSFieldVarchar
/** @var string - System name */
private string $Systemname = ''  ) {}

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
public function getsystemname(): string
{
    return $this->Systemname;
}
public function setsystemname(string $Systemname): self
{
    $this->Systemname = $Systemname;

    return $this;
}


  
}
