<?php
namespace ChameleonSystem\ImageHotspotBundle\Entity;

use ChameleonSystem\ImageHotspotBundle\Entity\pkgImageHotspotItem;
use ChameleonSystem\DataAccessBundle\Entity\CoreMedia\cmsMedia;

class pkgImageHotspotItemMarker {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var pkgImageHotspotItem|null - Belongs to hotspot image */
private ?pkgImageHotspotItem $PkgImageHotspotItem = null
, 
    // TCMSFieldVarchar
/** @var string - Alt or link text of the image */
private string $Name = '', 
    // TCMSFieldNumber
/** @var int - Position of top border relative to top border of background image */
private int $Top = 0, 
    // TCMSFieldNumber
/** @var int - Position of left border relative to left border of background image */
private int $Left = 0, 
    // TCMSFieldExtendedLookupMultiTable
/** @var string - Link to object */
private string $LinkedRecord = '',
// TCMSFieldExtendedLookupMultiTable
/** @var string - Link to object */
private string $LinkedRecordTableName = '', 
    // TCMSFieldURL
/** @var string - Alternative link */
private string $Url = '', 
    // TCMSFieldBoolean
/** @var bool - Show object layover */
private bool $ShowObjectLayover = false, 
    // TCMSFieldExtendedLookupMedia
/** @var cmsMedia|null - Image */
private ?cmsMedia $CmsMedia = null
, 
    // TCMSFieldExtendedLookupMedia
/** @var cmsMedia|null - Hover image */
private ?cmsMedia $CmsMediaHover = null
  ) {}

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
public function geturl(): string
{
    return $this->Url;
}
public function seturl(string $Url): self
{
    $this->Url = $Url;

    return $this;
}


  
    // TCMSFieldBoolean
public function isshowObjectLayover(): bool
{
    return $this->ShowObjectLayover;
}
public function setshowObjectLayover(bool $ShowObjectLayover): self
{
    $this->ShowObjectLayover = $ShowObjectLayover;

    return $this;
}


  
    // TCMSFieldExtendedLookupMedia
public function getCmsMedia(): ?cmsMedia
{
    return $this->CmsMedia;
}

public function setCmsMedia(?cmsMedia $CmsMedia): self
{
    $this->CmsMedia = $CmsMedia;

    return $this;
}


  
    // TCMSFieldExtendedLookupMedia
public function getCmsMediaHover(): ?cmsMedia
{
    return $this->CmsMediaHover;
}

public function setCmsMediaHover(?cmsMedia $CmsMediaHover): self
{
    $this->CmsMediaHover = $CmsMediaHover;

    return $this;
}


  
}
