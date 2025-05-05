<?php

namespace ChameleonSystem\ShopBundle\Entity\ProductList;

use ChameleonSystem\ShopBundle\Entity\Product\ShopArticle;

class ShopModuleArticleListArticle
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var ShopModuleArticleList|null - Belongs to article list */
        private ?ShopModuleArticleList $shopModuleArticleList = null,
        // TCMSFieldExtendedLookup
        /** @var ShopArticle|null - Article */
        private ?ShopArticle $shopArticle = null,
        // TCMSFieldPosition
        /** @var int - Position */
        private int $position = 0,
        // TCMSFieldVarchar
        /** @var string - Alternative headline */
        private string $name = ''
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
    public function getShopModuleArticleList(): ?ShopModuleArticleList
    {
        return $this->shopModuleArticleList;
    }

    public function setShopModuleArticleList(?ShopModuleArticleList $shopModuleArticleList): self
    {
        $this->shopModuleArticleList = $shopModuleArticleList;

        return $this;
    }

    // TCMSFieldExtendedLookup
    public function getShopArticle(): ?ShopArticle
    {
        return $this->shopArticle;
    }

    public function setShopArticle(?ShopArticle $shopArticle): self
    {
        $this->shopArticle = $shopArticle;

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
    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
