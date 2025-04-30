<?php

namespace ChameleonSystem\ShopRatingServiceBundle\Entity;

use ChameleonSystem\ExtranetBundle\Entity\DataExtranetUser;
use ChameleonSystem\ShopBundle\Entity\ShopOrder\ShopOrder;

class PkgShopRatingServiceHistory
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldExtendedLookup
        /** @var DataExtranetUser|null - User */
        private ?DataExtranetUser $dataExtranetUser = null,
        // TCMSFieldLookup
        /** @var ShopOrder|null - Belongs to order */
        private ?ShopOrder $shopOrder = null,
        // TCMSFieldDateTime
        /** @var \DateTime|null - Date */
        private ?\DateTime $date = null,
        // TCMSFieldVarchar
        /** @var string - List of rating services */
        private string $pkgShopRatingServiceIdList = ''
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

    // TCMSFieldExtendedLookup
    public function getDataExtranetUser(): ?DataExtranetUser
    {
        return $this->dataExtranetUser;
    }

    public function setDataExtranetUser(?DataExtranetUser $dataExtranetUser): self
    {
        $this->dataExtranetUser = $dataExtranetUser;

        return $this;
    }

    // TCMSFieldLookup
    public function getShopOrder(): ?ShopOrder
    {
        return $this->shopOrder;
    }

    public function setShopOrder(?ShopOrder $shopOrder): self
    {
        $this->shopOrder = $shopOrder;

        return $this;
    }

    // TCMSFieldDateTime
    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(?\DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }

    // TCMSFieldVarchar
    public function getPkgShopRatingServiceIdList(): string
    {
        return $this->pkgShopRatingServiceIdList;
    }

    public function setPkgShopRatingServiceIdList(string $pkgShopRatingServiceIdList): self
    {
        $this->pkgShopRatingServiceIdList = $pkgShopRatingServiceIdList;

        return $this;
    }
}
