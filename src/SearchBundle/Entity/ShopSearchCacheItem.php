<?php

namespace ChameleonSystem\SearchBundle\Entity;

use ChameleonSystem\ShopBundle\Entity\Product\ShopArticle;

class ShopSearchCacheItem
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var ShopSearchCache|null - Belongs to search cache */
        private ?ShopSearchCache $shopSearchCache = null
        ,
        // TCMSFieldDecimal
        /** @var float - Weight */
        private float $weight = 0,
        // TCMSFieldExtendedLookup
        /** @var ShopArticle|null - Article */
        private ?ShopArticle $shopArticle = null
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
    public function getShopSearchCache(): ?ShopSearchCache
    {
        return $this->shopSearchCache;
    }

    public function setShopSearchCache(?ShopSearchCache $shopSearchCache): self
    {
        $this->shopSearchCache = $shopSearchCache;

        return $this;
    }


    // TCMSFieldDecimal
    public function getWeight(): float
    {
        return $this->weight;
    }

    public function setWeight(float $weight): self
    {
        $this->weight = $weight;

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


}
