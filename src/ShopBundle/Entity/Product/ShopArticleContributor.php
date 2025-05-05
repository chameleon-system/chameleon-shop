<?php

namespace ChameleonSystem\ShopBundle\Entity\Product;

class ShopArticleContributor
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var ShopArticle|null - Belongs to article */
        private ?ShopArticle $shopArticle = null,
        // TCMSFieldExtendedLookup
        /** @var ShopContributor|null - Contributing person */
        private ?ShopContributor $shopContributor = null,
        // TCMSFieldLookup
        /** @var ShopContributorType|null - Role of the contributing person / contribution type */
        private ?ShopContributorType $shopContributorType = null,
        // TCMSFieldPosition
        /** @var int - Position */
        private int $position = 0
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
    public function getShopArticle(): ?ShopArticle
    {
        return $this->shopArticle;
    }

    public function setShopArticle(?ShopArticle $shopArticle): self
    {
        $this->shopArticle = $shopArticle;

        return $this;
    }

    // TCMSFieldExtendedLookup
    public function getShopContributor(): ?ShopContributor
    {
        return $this->shopContributor;
    }

    public function setShopContributor(?ShopContributor $shopContributor): self
    {
        $this->shopContributor = $shopContributor;

        return $this;
    }

    // TCMSFieldLookup
    public function getShopContributorType(): ?ShopContributorType
    {
        return $this->shopContributorType;
    }

    public function setShopContributorType(?ShopContributorType $shopContributorType): self
    {
        $this->shopContributorType = $shopContributorType;

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
}
