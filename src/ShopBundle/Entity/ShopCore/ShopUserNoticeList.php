<?php

namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

use ChameleonSystem\ExtranetBundle\Entity\DataExtranetUser;
use ChameleonSystem\ShopBundle\Entity\Product\ShopArticle;
use DateTime;

class ShopUserNoticeList
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var DataExtranetUser|null - Belongs to customer */
        private ?DataExtranetUser $dataExtranetUser = null
        ,
        // TCMSFieldDateTime
        /** @var DateTime|null - Added */
        private ?DateTime $dateAdded = null,
        // TCMSFieldExtendedLookup
        /** @var ShopArticle|null - Article */
        private ?ShopArticle $shopArticle = null
        ,
        // TCMSFieldDecimal
        /** @var string - Units */
        private string $amount = '1'
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


    // TCMSFieldDateTime
    public function getDateAdded(): ?DateTime
    {
        return $this->dateAdded;
    }

    public function setDateAdded(?DateTime $dateAdded): self
    {
        $this->dateAdded = $dateAdded;

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


    // TCMSFieldDecimal
    public function getAmount(): string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }


}
