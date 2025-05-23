<?php

namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

use ChameleonSystem\DataAccessBundle\Entity\CorePagedef\CmsTree;

class ShopSystemPage
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var Shop|null - Belongs to shop */
        private ?Shop $shop = null,
        // TCMSFieldVarchar
        /** @var string - Internal system name */
        private string $nameInternal = '',
        // TCMSFieldVarchar
        /** @var string - Display name */
        private string $name = '',
        // TCMSFieldTreeNode
        /** @var CmsTree|null - Navigation item (node) */
        private ?CmsTree $cmsTree = null
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
    public function getShop(): ?Shop
    {
        return $this->shop;
    }

    public function setShop(?Shop $shop): self
    {
        $this->shop = $shop;

        return $this;
    }

    // TCMSFieldVarchar
    public function getNameInternal(): string
    {
        return $this->nameInternal;
    }

    public function setNameInternal(string $nameInternal): self
    {
        $this->nameInternal = $nameInternal;

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

    // TCMSFieldTreeNode
    public function getCmsTree(): ?CmsTree
    {
        return $this->cmsTree;
    }

    public function setCmsTree(?CmsTree $cmsTree): self
    {
        $this->cmsTree = $cmsTree;

        return $this;
    }
}
