<?php

namespace ChameleonSystem\ShopBundle\Entity\ShopVoucher;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ShopVoucher
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var ShopVoucherSeries|null - Belongs to voucher series */
        private ?ShopVoucherSeries $shopVoucherSeries = null,
        // TCMSFieldVarchar
        /** @var string - Code */
        private string $code = '',
        // TCMSFieldDateTime
        /** @var \DateTime|null - Created on */
        private ?\DateTime $datecreated = null,
        // TCMSFieldDateTime
        /** @var \DateTime|null - Used up on */
        private ?\DateTime $dateUsedUp = null,
        // TCMSFieldBoolean
        /** @var bool - Is used up */
        private bool $isUsedUp = false,
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopVoucherUse> - Voucher usages */
        private Collection $shopVoucherUseCollection = new ArrayCollection()
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
    public function getShopVoucherSeries(): ?ShopVoucherSeries
    {
        return $this->shopVoucherSeries;
    }

    public function setShopVoucherSeries(?ShopVoucherSeries $shopVoucherSeries): self
    {
        $this->shopVoucherSeries = $shopVoucherSeries;

        return $this;
    }

    // TCMSFieldVarchar
    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    // TCMSFieldDateTime
    public function getDatecreated(): ?\DateTime
    {
        return $this->datecreated;
    }

    public function setDatecreated(?\DateTime $datecreated): self
    {
        $this->datecreated = $datecreated;

        return $this;
    }

    // TCMSFieldDateTime
    public function getDateUsedUp(): ?\DateTime
    {
        return $this->dateUsedUp;
    }

    public function setDateUsedUp(?\DateTime $dateUsedUp): self
    {
        $this->dateUsedUp = $dateUsedUp;

        return $this;
    }

    // TCMSFieldBoolean
    public function isIsUsedUp(): bool
    {
        return $this->isUsedUp;
    }

    public function setIsUsedUp(bool $isUsedUp): self
    {
        $this->isUsedUp = $isUsedUp;

        return $this;
    }

    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, ShopVoucherUse>
     */
    public function getShopVoucherUseCollection(): Collection
    {
        return $this->shopVoucherUseCollection;
    }

    public function addShopVoucherUseCollection(ShopVoucherUse $shopVoucherUse): self
    {
        if (!$this->shopVoucherUseCollection->contains($shopVoucherUse)) {
            $this->shopVoucherUseCollection->add($shopVoucherUse);
            $shopVoucherUse->setShopVoucher($this);
        }

        return $this;
    }

    public function removeShopVoucherUseCollection(ShopVoucherUse $shopVoucherUse): self
    {
        if ($this->shopVoucherUseCollection->removeElement($shopVoucherUse)) {
            // set the owning side to null (unless already changed)
            if ($shopVoucherUse->getShopVoucher() === $this) {
                $shopVoucherUse->setShopVoucher(null);
            }
        }

        return $this;
    }
}
