<?php

namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

use ChameleonSystem\DataAccessBundle\Entity\CorePagedef\CmsTplModuleInstance;

class PkgShopListfilterModuleConfig
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var CmsTplModuleInstance|null - Belongs to module instance */
        private ?CmsTplModuleInstance $cmsTplModuleInstance = null
        ,
        // TCMSFieldExtendedLookup
        /** @var PkgShopListfilter|null - */
        private ?PkgShopListfilter $pkgShopListfilter = null
        ,
        // TCMSFieldText
        /** @var string - Filter parameters */
        private string $filterParameter = ''
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


    // TCMSFieldExtendedLookup
    public function getPkgShopListfilter(): ?PkgShopListfilter
    {
        return $this->pkgShopListfilter;
    }

    public function setPkgShopListfilter(?PkgShopListfilter $pkgShopListfilter): self
    {
        $this->pkgShopListfilter = $pkgShopListfilter;

        return $this;
    }


    // TCMSFieldText
    public function getFilterParameter(): string
    {
        return $this->filterParameter;
    }

    public function setFilterParameter(string $filterParameter): self
    {
        $this->filterParameter = $filterParameter;

        return $this;
    }


}
