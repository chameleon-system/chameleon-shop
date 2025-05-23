<?php

namespace ChameleonSystem\ShopRatingServiceBundle\Entity;

use ChameleonSystem\DataAccessBundle\Entity\CorePagedef\CmsTplModuleInstance;

class PkgShopRatingServiceWidgetConfig
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var CmsTplModuleInstance|null - Module instance */
        private ?CmsTplModuleInstance $cmsTplModuleInstance = null,
        // TCMSFieldLookup
        /** @var PkgShopRatingService|null - Rating service */
        private ?PkgShopRatingService $pkgShopRatingService = null
    ) {
    }

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
    public function getCmsTplModuleInstance(): ?CmsTplModuleInstance
    {
        return $this->cmsTplModuleInstance;
    }

    public function setCmsTplModuleInstance(?CmsTplModuleInstance $cmsTplModuleInstance): self
    {
        $this->cmsTplModuleInstance = $cmsTplModuleInstance;

        return $this;
    }

    // TCMSFieldLookup
    public function getPkgShopRatingService(): ?PkgShopRatingService
    {
        return $this->pkgShopRatingService;
    }

    public function setPkgShopRatingService(?PkgShopRatingService $pkgShopRatingService): self
    {
        $this->pkgShopRatingService = $pkgShopRatingService;

        return $this;
    }
}
