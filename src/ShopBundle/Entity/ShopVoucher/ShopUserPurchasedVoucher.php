<?php

namespace ChameleonSystem\ShopBundle\Entity\ShopVoucher;

use ChameleonSystem\ExtranetBundle\Entity\DataExtranetUser;
use DateTime;

class ShopUserPurchasedVoucher
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var DataExtranetUser|null - Belongs to customer */
        private ?DataExtranetUser $dataExtranetUser = null
        ,
        // TCMSFieldExtendedLookup
        /** @var ShopVoucher|null - Voucher */
        private ?ShopVoucher $shopVoucher = null
        ,
        // TCMSFieldDateTime
        /** @var DateTime|null - Bought on */
        private ?DateTime $datePurchased = null
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
    public function getDataExtranetUser(): ?DataExtranetUser
    {
        return $this->dataExtranetUser;
    }

    public function setDataExtranetUser(?DataExtranetUser $dataExtranetUser): self
    {
        $this->dataExtranetUser = $dataExtranetUser;

        return $this;
    }


    // TCMSFieldExtendedLookup
    public function getShopVoucher(): ?ShopVoucher
    {
        return $this->shopVoucher;
    }

    public function setShopVoucher(?ShopVoucher $shopVoucher): self
    {
        $this->shopVoucher = $shopVoucher;

        return $this;
    }


    // TCMSFieldDateTime
    public function getDatePurchased(): ?DateTime
    {
        return $this->datePurchased;
    }

    public function setDatePurchased(?DateTime $datePurchased): self
    {
        $this->datePurchased = $datePurchased;

        return $this;
    }


}
