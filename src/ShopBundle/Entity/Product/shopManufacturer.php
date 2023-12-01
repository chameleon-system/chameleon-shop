<?php
namespace ChameleonSystem\ShopBundle\Entity\Product;


class shopManufacturer {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldVarchar
/** @var string - Name */
private string $Name = '', 
    // TCMSFieldBoolean
/** @var bool - Active */
private bool $Active = true, 
    // TCMSFieldPosition
/** @var int - Position */
private int $Position = 0, 
    // TCMSFieldVarchar
/** @var string - Short description */
private string $DescriptionShort = '', 
    // TCMSFieldMedia
/** @var array<string> - Icon / logo */
private array $CmsMediaId = ['1', '1'], 
    // TCMSFieldColorpicker
/** @var string - Color */
private string $Color = '', 
    // TCMSFieldVarchar
/** @var string - CSS file for manufacturer page */
private string $Css = '', 
    // TCMSFieldWYSIWYG
/** @var string - Description */
private string $Description = '', 
    // TCMSFieldWYSIWYG
/** @var string - Size chart */
private string $Sizetable = ''  ) {}

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


  
    // TCMSFieldBoolean
public function isactive(): bool
{
    return $this->Active;
}
public function setactive(bool $Active): self
{
    $this->Active = $Active;

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


  
    // TCMSFieldVarchar
public function getdescriptionShort(): string
{
    return $this->DescriptionShort;
}
public function setdescriptionShort(string $DescriptionShort): self
{
    $this->DescriptionShort = $DescriptionShort;

    return $this;
}


  
    // TCMSFieldMedia
public function getcmsMediaId(): array
{
    return $this->CmsMediaId;
}
public function setcmsMediaId(array $CmsMediaId): self
{
    $this->CmsMediaId = $CmsMediaId;

    return $this;
}


  
    // TCMSFieldColorpicker
public function getcolor(): string
{
    return $this->Color;
}
public function setcolor(string $Color): self
{
    $this->Color = $Color;

    return $this;
}


  
    // TCMSFieldVarchar
public function getcss(): string
{
    return $this->Css;
}
public function setcss(string $Css): self
{
    $this->Css = $Css;

    return $this;
}


  
    // TCMSFieldWYSIWYG
public function getdescription(): string
{
    return $this->Description;
}
public function setdescription(string $Description): self
{
    $this->Description = $Description;

    return $this;
}


  
    // TCMSFieldWYSIWYG
public function getsizetable(): string
{
    return $this->Sizetable;
}
public function setsizetable(string $Sizetable): self
{
    $this->Sizetable = $Sizetable;

    return $this;
}


  
}
