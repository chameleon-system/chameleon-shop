<?php
namespace ChameleonSystem\ShopBundle\Entity\ProductList;


class shopModuleArticlelistOrderby {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldVarchar
/** @var string - Internal name */
private string $Internalname = '', 
    // TCMSFieldVarchar
/** @var string - Public name */
private string $NamePublic = '', 
    // TCMSFieldVarchar
/** @var string - Name */
private string $Name = '', 
    // TCMSFieldPosition
/** @var int - Position */
private int $Position = 0, 
    // TCMSFieldVarchar
/** @var string - SQL ORDER BY String */
private string $SqlOrderBy = '', 
    // TCMSFieldOption
/** @var string - Sorting direction */
private string $OrderDirection = 'ASC', 
    // TCMSFieldText
/** @var string - SQL secondary sorting */
private string $SqlSecondaryOrderByString = ''  ) {}

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
public function getinternalname(): string
{
    return $this->Internalname;
}
public function setinternalname(string $Internalname): self
{
    $this->Internalname = $Internalname;

    return $this;
}


  
    // TCMSFieldVarchar
public function getnamePublic(): string
{
    return $this->NamePublic;
}
public function setnamePublic(string $NamePublic): self
{
    $this->NamePublic = $NamePublic;

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
public function getsqlOrderBy(): string
{
    return $this->SqlOrderBy;
}
public function setsqlOrderBy(string $SqlOrderBy): self
{
    $this->SqlOrderBy = $SqlOrderBy;

    return $this;
}


  
    // TCMSFieldOption
public function getorderDirection(): string
{
    return $this->OrderDirection;
}
public function setorderDirection(string $OrderDirection): self
{
    $this->OrderDirection = $OrderDirection;

    return $this;
}


  
    // TCMSFieldText
public function getsqlSecondaryOrderByString(): string
{
    return $this->SqlSecondaryOrderByString;
}
public function setsqlSecondaryOrderByString(string $SqlSecondaryOrderByString): self
{
    $this->SqlSecondaryOrderByString = $SqlSecondaryOrderByString;

    return $this;
}


  
}
