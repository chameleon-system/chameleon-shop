<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopCore;


class pkgShopStatisticGroup {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldVarchar
/** @var string - Field with date */
private string $DateRestrictionField = '`shop_order`.`datecreated`', 
    // TCMSFieldVarchar
/** @var string - Groups */
private string $Groups = '', 
    // TCMSFieldText
/** @var string - Query */
private string $Query = '', 
    // TCMSFieldVarchar
/** @var string - Field with portal limitation */
private string $PortalRestrictionField = '', 
    // TCMSFieldVarchar
/** @var string - Name */
private string $Name = '', 
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
    // TCMSFieldVarchar
public function getdateRestrictionField(): string
{
    return $this->DateRestrictionField;
}
public function setdateRestrictionField(string $DateRestrictionField): self
{
    $this->DateRestrictionField = $DateRestrictionField;

    return $this;
}


  
    // TCMSFieldVarchar
public function getgroups(): string
{
    return $this->Groups;
}
public function setgroups(string $Groups): self
{
    $this->Groups = $Groups;

    return $this;
}


  
    // TCMSFieldText
public function getquery(): string
{
    return $this->Query;
}
public function setquery(string $Query): self
{
    $this->Query = $Query;

    return $this;
}


  
    // TCMSFieldVarchar
public function getportalRestrictionField(): string
{
    return $this->PortalRestrictionField;
}
public function setportalRestrictionField(string $PortalRestrictionField): self
{
    $this->PortalRestrictionField = $PortalRestrictionField;

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


  
}
