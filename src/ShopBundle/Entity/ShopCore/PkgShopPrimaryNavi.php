<?php

namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

use ChameleonSystem\DataAccessBundle\Entity\CorePortal\CmsPortal;

class PkgShopPrimaryNavi
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var CmsPortal|null - Belongs to portal */
        private ?CmsPortal $cmsPortal = null,
        // TCMSFieldVarchar
        /** @var string - Name */
        private string $name = '',
        // TCMSFieldBoolean
        /** @var bool - Active */
        private bool $active = false,
        // TCMSFieldPosition
        /** @var int - Position */
        private int $position = 0,
        // TCMSFieldExtendedLookupMultiTable
        /** @var string - Select navigation */
        private string $target = '',
        // TCMSFieldExtendedLookupMultiTable
        /** @var string - Select navigation */
        private string $targetTableName = '',
        // TCMSFieldBoolean
        /** @var bool - Replace submenu with shop main categories */
        private bool $showRootCategoryTree = false,
        // TCMSFieldVarchar
        /** @var string - Individual CSS class */
        private string $cssClass = ''
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
    public function getCmsPortal(): ?CmsPortal
    {
        return $this->cmsPortal;
    }

    public function setCmsPortal(?CmsPortal $cmsPortal): self
    {
        $this->cmsPortal = $cmsPortal;

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

    // TCMSFieldBoolean
    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

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

    // TCMSFieldExtendedLookupMultiTable
    public function getTarget(): string
    {
        return $this->target;
    }

    public function setTarget(string $target): self
    {
        $this->target = $target;

        return $this;
    }

    // TCMSFieldExtendedLookupMultiTable
    public function getTargetTableName(): string
    {
        return $this->targetTableName;
    }

    public function setTargetTableName(string $targetTableName): self
    {
        $this->targetTableName = $targetTableName;

        return $this;
    }

    // TCMSFieldBoolean
    public function isShowRootCategoryTree(): bool
    {
        return $this->showRootCategoryTree;
    }

    public function setShowRootCategoryTree(bool $showRootCategoryTree): self
    {
        $this->showRootCategoryTree = $showRootCategoryTree;

        return $this;
    }

    // TCMSFieldVarchar
    public function getCssClass(): string
    {
        return $this->cssClass;
    }

    public function setCssClass(string $cssClass): self
    {
        $this->cssClass = $cssClass;

        return $this;
    }
}
