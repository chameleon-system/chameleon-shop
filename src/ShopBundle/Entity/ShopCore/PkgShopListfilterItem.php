<?php

namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

use ChameleonSystem\ShopBundle\Entity\Product\ShopAttribute;

class PkgShopListfilterItem
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var PkgShopListfilter|null - Belongs to list filter configuration */
        private ?PkgShopListfilter $pkgShopListfilter = null
        ,
        // TCMSFieldLookup
        /** @var PkgShopListfilterItemType|null - Filter type */
        private ?PkgShopListfilterItemType $pkgShopListfilterItemType = null
        ,
        // TCMSFieldVarchar
        /** @var string - Name */
        private string $name = '',
        // TCMSFieldVarchar
        /** @var string - System name */
        private string $systemname = '',
        // TCMSFieldExtendedLookup
        /** @var ShopAttribute|null - Belonging product attribute */
        private ?ShopAttribute $shopAttribute = null
        ,
        // TCMSFieldBoolean
        /** @var bool - Multiple selections */
        private bool $allowMultiSelection = false,
        // TCMSFieldBoolean
        /** @var bool - Show all when opening the page? */
        private bool $showAllOnPageLoad = true,
        // TCMSFieldNumber
        /** @var int - Window size */
        private int $previewSize = 0,
        // TCMSFieldBoolean
        /** @var bool - Show scrollbars instead of "show all" button? */
        private bool $showScrollbars = false,
        // TCMSFieldNumber
        /** @var int - Lowest value */
        private int $minValue = 0,
        // TCMSFieldNumber
        /** @var int - Highest value */
        private int $maxValue = 0,
        // TCMSFieldVarchar
        /** @var string - MySQL field name */
        private string $mysqlFieldName = '',
        // TCMSFieldVarchar
        /** @var string - View */
        private string $view = '',
        // TCMSFieldOption
        /** @var string - View class type */
        private string $viewClassType = 'Customer',
        // TCMSFieldPosition
        /** @var int - Sorting */
        private int $position = 0,
        // TCMSFieldVarchar
        /** @var string - System name of the variant type */
        private string $variantIdentifier = ''
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
    public function getPkgShopListfilter(): ?PkgShopListfilter
    {
        return $this->pkgShopListfilter;
    }

    public function setPkgShopListfilter(?PkgShopListfilter $pkgShopListfilter): self
    {
        $this->pkgShopListfilter = $pkgShopListfilter;

        return $this;
    }


    // TCMSFieldLookup
    public function getPkgShopListfilterItemType(): ?PkgShopListfilterItemType
    {
        return $this->pkgShopListfilterItemType;
    }

    public function setPkgShopListfilterItemType(?PkgShopListfilterItemType $pkgShopListfilterItemType): self
    {
        $this->pkgShopListfilterItemType = $pkgShopListfilterItemType;

        return $this;
    }


    // TCMSFieldVarchar
    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }


    // TCMSFieldVarchar
    public function getSystemname(): string
    {
        return $this->systemname;
    }

    public function setSystemname(string $systemname): self
    {
        $this->systemname = $systemname;

        return $this;
    }


    // TCMSFieldExtendedLookup
    public function getShopAttribute(): ?ShopAttribute
    {
        return $this->shopAttribute;
    }

    public function setShopAttribute(?ShopAttribute $shopAttribute): self
    {
        $this->shopAttribute = $shopAttribute;

        return $this;
    }


    // TCMSFieldBoolean
    public function isAllowMultiSelection(): bool
    {
        return $this->allowMultiSelection;
    }

    public function setAllowMultiSelection(bool $allowMultiSelection): self
    {
        $this->allowMultiSelection = $allowMultiSelection;

        return $this;
    }


    // TCMSFieldBoolean
    public function isShowAllOnPageLoad(): bool
    {
        return $this->showAllOnPageLoad;
    }

    public function setShowAllOnPageLoad(bool $showAllOnPageLoad): self
    {
        $this->showAllOnPageLoad = $showAllOnPageLoad;

        return $this;
    }


    // TCMSFieldNumber
    public function getPreviewSize(): int
    {
        return $this->previewSize;
    }

    public function setPreviewSize(int $previewSize): self
    {
        $this->previewSize = $previewSize;

        return $this;
    }


    // TCMSFieldBoolean
    public function isShowScrollbars(): bool
    {
        return $this->showScrollbars;
    }

    public function setShowScrollbars(bool $showScrollbars): self
    {
        $this->showScrollbars = $showScrollbars;

        return $this;
    }


    // TCMSFieldNumber
    public function getMinValue(): int
    {
        return $this->minValue;
    }

    public function setMinValue(int $minValue): self
    {
        $this->minValue = $minValue;

        return $this;
    }


    // TCMSFieldNumber
    public function getMaxValue(): int
    {
        return $this->maxValue;
    }

    public function setMaxValue(int $maxValue): self
    {
        $this->maxValue = $maxValue;

        return $this;
    }


    // TCMSFieldVarchar
    public function getMysqlFieldName(): string
    {
        return $this->mysqlFieldName;
    }

    public function setMysqlFieldName(string $mysqlFieldName): self
    {
        $this->mysqlFieldName = $mysqlFieldName;

        return $this;
    }


    // TCMSFieldVarchar
    public function getView(): string
    {
        return $this->view;
    }

    public function setView(string $view): self
    {
        $this->view = $view;

        return $this;
    }


    // TCMSFieldOption
    public function getViewClassType(): string
    {
        return $this->viewClassType;
    }

    public function setViewClassType(string $viewClassType): self
    {
        $this->viewClassType = $viewClassType;

        return $this;
    }


    // TCMSFieldPosition
    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }


    // TCMSFieldVarchar
    public function getVariantIdentifier(): string
    {
        return $this->variantIdentifier;
    }

    public function setVariantIdentifier(string $variantIdentifier): self
    {
        $this->variantIdentifier = $variantIdentifier;

        return $this;
    }


}
