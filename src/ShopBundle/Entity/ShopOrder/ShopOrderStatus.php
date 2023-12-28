<?php

namespace ChameleonSystem\ShopBundle\Entity\ShopOrder;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ShopOrderStatus
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var ShopOrder|null - Belongs to order */
        private ?ShopOrder $shopOrder = null
        ,
        // TCMSFieldDateTimeNow
        /** @var DateTime|null - Date */
        private ?DateTime $statusDate = new DateTime(),
        // TCMSFieldLookup
        /** @var ShopOrderStatusCode|null - Status code */
        private ?ShopOrderStatusCode $shopOrderStatusCode = null
        ,
        // TCMSFieldBlob
        /** @var object|null - Data */
        private ?object $data = null,
        // TCMSFieldWYSIWYG
        /** @var string - Additional info */
        private string $info = '',
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopOrderStatusItem> - Order status items */
        private Collection $shopOrderStatusItemCollection = new ArrayCollection()
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
    public function getShopOrder(): ?ShopOrder
    {
        return $this->shopOrder;
    }

    public function setShopOrder(?ShopOrder $shopOrder): self
    {
        $this->shopOrder = $shopOrder;

        return $this;
    }


    // TCMSFieldDateTimeNow
    public function getStatusDate(): ?DateTime
    {
        return $this->statusDate;
    }

    public function setStatusDate(?DateTime $statusDate): self
    {
        $this->statusDate = $statusDate;

        return $this;
    }


    // TCMSFieldLookup
    public function getShopOrderStatusCode(): ?ShopOrderStatusCode
    {
        return $this->shopOrderStatusCode;
    }

    public function setShopOrderStatusCode(?ShopOrderStatusCode $shopOrderStatusCode): self
    {
        $this->shopOrderStatusCode = $shopOrderStatusCode;

        return $this;
    }


    // TCMSFieldBlob
    public function getData(): ?object
    {
        return $this->data;
    }

    public function setData(?object $data): self
    {
        $this->data = $data;

        return $this;
    }


    // TCMSFieldWYSIWYG
    public function getInfo(): string
    {
        return $this->info;
    }

    public function setInfo(string $info): self
    {
        $this->info = $info;

        return $this;
    }



    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, ShopOrderStatusItem>
     */
    public function getShopOrderStatusItemCollection(): Collection
    {
        return $this->shopOrderStatusItemCollection;
    }

    public function addShopOrderStatusItemCollection(ShopOrderStatusItem $shopOrderStatusItem): self
    {
        if (!$this->shopOrderStatusItemCollection->contains($shopOrderStatusItem)) {
            $this->shopOrderStatusItemCollection->add($shopOrderStatusItem);
            $shopOrderStatusItem->setShopOrderStatus($this);
        }

        return $this;
    }

    public function removeShopOrderStatusItemCollection(ShopOrderStatusItem $shopOrderStatusItem): self
    {
        if ($this->shopOrderStatusItemCollection->removeElement($shopOrderStatusItem)) {
            // set the owning side to null (unless already changed)
            if ($shopOrderStatusItem->getShopOrderStatus() === $this) {
                $shopOrderStatusItem->setShopOrderStatus(null);
            }
        }

        return $this;
    }


}
