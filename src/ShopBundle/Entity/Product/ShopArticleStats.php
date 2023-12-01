<?php

namespace ChameleonSystem\ShopBundle\Entity\Product;

class ShopArticleStats
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var ShopArticle|null - Belongs to */
        private ?ShopArticle $shopArticle = null
        ,
        // TCMSFieldNumber
        /** @var int - Sales */
        private int $statsSales = 0,
        // TCMSFieldNumber
        /** @var int - Details on views */
        private int $statsDetailViews = 0,
        // TCMSFieldDecimal
        /** @var float - Average rating */
        private float $statsReviewAverage = 0,
        // TCMSFieldNumber
        /** @var int - Number of ratings */
        private int $statsReviewCount = 0
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


    // TCMSFieldNumber
    public function getStatsSales(): int
    {
        return $this->statsSales;
    }

    public function setStatsSales(int $statsSales): self
    {
        $this->statsSales = $statsSales;

        return $this;
    }


    // TCMSFieldNumber
    public function getStatsDetailViews(): int
    {
        return $this->statsDetailViews;
    }

    public function setStatsDetailViews(int $statsDetailViews): self
    {
        $this->statsDetailViews = $statsDetailViews;

        return $this;
    }


    // TCMSFieldDecimal
    public function getStatsReviewAverage(): float
    {
        return $this->statsReviewAverage;
    }

    public function setStatsReviewAverage(float $statsReviewAverage): self
    {
        $this->statsReviewAverage = $statsReviewAverage;

        return $this;
    }


    // TCMSFieldNumber
    public function getStatsReviewCount(): int
    {
        return $this->statsReviewCount;
    }

    public function setStatsReviewCount(int $statsReviewCount): self
    {
        $this->statsReviewCount = $statsReviewCount;

        return $this;
    }


}
