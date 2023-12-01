<?php
namespace ChameleonSystem\ImageHotspotBundle\Entity;

use ChameleonSystem\ImageHotspotBundle\Entity\pkgImageHotspotItem;

class pkgImageHotspotItemSpot {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var pkgImageHotspotItem|null - Belongs to hotspot image */
private ?pkgImageHotspotItem $PkgImageHotspotItem = null
, 
    // TCMSFieldNumber
/** @var int - Distance top */
private int $Top = 0, 
    // TCMSFieldNumber
/** @var int - Distance left */
private int $Left = 0, 
    // TCMSFieldOption
/** @var string - Hotspot icon type */
private string $HotspotType = 'Hotspot-Rechts', 
    // TCMSFieldExtendedLookupMultiTable
/** @var string - Linked CMS object */
private string $LinkedRecord = '',
// TCMSFieldExtendedLookupMultiTable
/** @var string - Linked CMS object */
private string $LinkedRecordTableName = '', 
    // TCMSFieldURL
/** @var string - External URL */
private string $ExternalUrl = '', 
    // TCMSFieldText
/** @var string - Polygon area */
private string $PolygonArea = '', 
    // TCMSFieldBoolean
/** @var bool - Show product info layover */
private bool $ShowSpot = true  ) {}

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
    // TCMSFieldLookupParentID
public function getPkgImageHotspotItem(): ?pkgImageHotspotItem
{
    return $this->PkgImageHotspotItem;
}

public function setPkgImageHotspotItem(?pkgImageHotspotItem $PkgImageHotspotItem): self
{
    $this->PkgImageHotspotItem = $PkgImageHotspotItem;

    return $this;
}


  
    // TCMSFieldNumber
public function gettop(): int
{
    return $this->Top;
}
public function settop(int $Top): self
{
    $this->Top = $Top;

    return $this;
}


  
    // TCMSFieldNumber
public function getleft(): int
{
    return $this->Left;
}
public function setleft(int $Left): self
{
    $this->Left = $Left;

    return $this;
}


  
    // TCMSFieldOption
public function gethotspotType(): string
{
    return $this->HotspotType;
}
public function sethotspotType(string $HotspotType): self
{
    $this->HotspotType = $HotspotType;

    return $this;
}


  
    // TCMSFieldExtendedLookupMultiTable
public function getlinkedRecord(): string
{
    return $this->LinkedRecord;
}
public function setlinkedRecord(string $LinkedRecord): self
{
    $this->LinkedRecord = $LinkedRecord;

    return $this;
}
// TCMSFieldExtendedLookupMultiTable
public function getlinkedRecordTableName(): string
{
    return $this->LinkedRecordTableName;
}
public function setlinkedRecordTableName(string $LinkedRecordTableName): self
{
    $this->LinkedRecordTableName = $LinkedRecordTableName;

    return $this;
}


  
    // TCMSFieldURL
public function getexternalUrl(): string
{
    return $this->ExternalUrl;
}
public function setexternalUrl(string $ExternalUrl): self
{
    $this->ExternalUrl = $ExternalUrl;

    return $this;
}


  
    // TCMSFieldText
public function getpolygonArea(): string
{
    return $this->PolygonArea;
}
public function setpolygonArea(string $PolygonArea): self
{
    $this->PolygonArea = $PolygonArea;

    return $this;
}


  
    // TCMSFieldBoolean
public function isshowSpot(): bool
{
    return $this->ShowSpot;
}
public function setshowSpot(bool $ShowSpot): self
{
    $this->ShowSpot = $ShowSpot;

    return $this;
}


  
}
