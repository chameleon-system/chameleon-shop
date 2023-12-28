<?php

namespace ChameleonSystem\SearchBundle\Entity;

use ChameleonSystem\ShopBundle\Entity\ShopCore\Shop;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ShopSearchCache
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var Shop|null - Belongs to shop */
        private ?Shop $shop = null
        ,
        // TCMSFieldVarchar
        /** @var string - Search key */
        private string $searchkey = '',
        // TCMSFieldDateTime
        /** @var DateTime|null - Last used */
        private ?DateTime $lastUsedDate = null,
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopSearchCacheItem> - Results */
        private Collection $shopSearchCacheItemCollection = new ArrayCollection()
        ,
        // TCMSFieldText
        /** @var string - Category hits */
        private string $categoryHits = '',
        // TCMSFieldNumber
        /** @var int - Number of records found */
        private int $numberOfRecordsFound = -1
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
    public function getSearchkey(): string
    {
        return $this->searchkey;
    }

    public function setSearchkey(string $searchkey): self
    {
        $this->searchkey = $searchkey;

        return $this;
    }


    // TCMSFieldDateTime
    public function getLastUsedDate(): ?DateTime
    {
        return $this->lastUsedDate;
    }

    public function setLastUsedDate(?DateTime $lastUsedDate): self
    {
        $this->lastUsedDate = $lastUsedDate;

        return $this;
    }



    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, ShopSearchCacheItem>
     */
    public function getShopSearchCacheItemCollection(): Collection
    {
        return $this->shopSearchCacheItemCollection;
    }

    public function addShopSearchCacheItemCollection(ShopSearchCacheItem $shopSearchCacheItem): self
    {
        if (!$this->shopSearchCacheItemCollection->contains($shopSearchCacheItem)) {
            $this->shopSearchCacheItemCollection->add($shopSearchCacheItem);
            $shopSearchCacheItem->setShopSearchCache($this);
        }

        return $this;
    }

    public function removeShopSearchCacheItemCollection(ShopSearchCacheItem $shopSearchCacheItem): self
    {
        if ($this->shopSearchCacheItemCollection->removeElement($shopSearchCacheItem)) {
            // set the owning side to null (unless already changed)
            if ($shopSearchCacheItem->getShopSearchCache() === $this) {
                $shopSearchCacheItem->setShopSearchCache(null);
            }
        }

        return $this;
    }


    // TCMSFieldText
    public function getCategoryHits(): string
    {
        return $this->categoryHits;
    }

    public function setCategoryHits(string $categoryHits): self
    {
        $this->categoryHits = $categoryHits;

        return $this;
    }


    // TCMSFieldNumber
    public function getNumberOfRecordsFound(): int
    {
        return $this->numberOfRecordsFound;
    }

    public function setNumberOfRecordsFound(int $numberOfRecordsFound): self
    {
        $this->numberOfRecordsFound = $numberOfRecordsFound;

        return $this;
    }


}
