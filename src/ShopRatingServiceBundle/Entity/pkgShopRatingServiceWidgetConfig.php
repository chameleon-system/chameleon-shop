<?php
namespace ChameleonSystem\ShopRatingServiceBundle\Entity;

use ChameleonSystem\DataAccessBundle\Entity\CorePagedef\cmsTplModuleInstance;
use ChameleonSystem\ShopRatingServiceBundle\Entity\pkgShopRatingService;

class pkgShopRatingServiceWidgetConfig {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var cmsTplModuleInstance|null - Module instance */
private ?cmsTplModuleInstance $CmsTplModuleInstance = null
, 
    // TCMSFieldLookup
/** @var pkgShopRatingService|null - Rating service */
private ?pkgShopRatingService $PkgShopRatingService = null
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
public function getCmsTplModuleInstance(): ?cmsTplModuleInstance
{
    return $this->CmsTplModuleInstance;
}

public function setCmsTplModuleInstance(?cmsTplModuleInstance $CmsTplModuleInstance): self
{
    $this->CmsTplModuleInstance = $CmsTplModuleInstance;

    return $this;
}


  
    // TCMSFieldLookup
public function getPkgShopRatingService(): ?pkgShopRatingService
{
    return $this->PkgShopRatingService;
}

public function setPkgShopRatingService(?pkgShopRatingService $PkgShopRatingService): self
{
    $this->PkgShopRatingService = $PkgShopRatingService;

    return $this;
}


  
}
