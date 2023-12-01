<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

use ChameleonSystem\DataAccessBundle\Entity\CorePagedef\cmsTplModuleInstance;
use ChameleonSystem\ShopBundle\Entity\ShopCore\pkgShopListfilter;

class pkgShopListfilterModuleConfig {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var cmsTplModuleInstance|null - Belongs to module instance */
private ?cmsTplModuleInstance $CmsTplModuleInstance = null
, 
    // TCMSFieldExtendedLookup
/** @var pkgShopListfilter|null -  */
private ?pkgShopListfilter $PkgShopListfilter = null
, 
    // TCMSFieldText
/** @var string - Filter parameters */
private string $FilterParameter = ''  ) {}

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


  
    // TCMSFieldExtendedLookup
public function getPkgShopListfilter(): ?pkgShopListfilter
{
    return $this->PkgShopListfilter;
}

public function setPkgShopListfilter(?pkgShopListfilter $PkgShopListfilter): self
{
    $this->PkgShopListfilter = $PkgShopListfilter;

    return $this;
}


  
    // TCMSFieldText
public function getfilterParameter(): string
{
    return $this->FilterParameter;
}
public function setfilterParameter(string $FilterParameter): self
{
    $this->FilterParameter = $FilterParameter;

    return $this;
}


  
}
