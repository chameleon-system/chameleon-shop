<?php
namespace ChameleonSystem\ShopBundle\Entity\Product;

use ChameleonSystem\DataAccessBundle\Entity\CorePagedef\cmsTplModuleInstance;
use ChameleonSystem\DataAccessBundle\Entity\CoreMedia\cmsMedia;

class shopManufacturerModuleConf {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var cmsTplModuleInstance|null - Belongs to module instance */
private ?cmsTplModuleInstance $CmsTplModuleInstance = null
, 
    // TCMSFieldVarchar
/** @var string - Title / headline */
private string $Name = '', 
    // TCMSFieldExtendedLookupMedia
/** @var cmsMedia|null - Icon */
private ?cmsMedia $CmsMedia = null
, 
    // TCMSFieldWYSIWYG
/** @var string - Introduction text */
private string $Intro = ''  ) {}

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
public function getCmsTplModuleInstance(): ?cmsTplModuleInstance
{
    return $this->CmsTplModuleInstance;
}

public function setCmsTplModuleInstance(?cmsTplModuleInstance $CmsTplModuleInstance): self
{
    $this->CmsTplModuleInstance = $CmsTplModuleInstance;

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


  
    // TCMSFieldWYSIWYG
public function getintro(): string
{
    return $this->Intro;
}
public function setintro(string $Intro): self
{
    $this->Intro = $Intro;

    return $this;
}


  
}
