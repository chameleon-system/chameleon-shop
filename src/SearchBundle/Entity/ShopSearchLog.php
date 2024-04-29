<?php

namespace ChameleonSystem\SearchBundle\Entity;

use ChameleonSystem\DataAccessBundle\Entity\Core\CmsLanguage;
use ChameleonSystem\ExtranetBundle\Entity\DataExtranetUser;
use ChameleonSystem\ShopBundle\Entity\ShopCore\Shop;
use DateTime;

class ShopSearchLog
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var Shop|null - Belongs to shop */
        private ?Shop $shop = null
        ,
        // TCMSFieldExtendedLookup
        /** @var CmsLanguage|null - Language */
        private ?CmsLanguage $cmsLanguage = null
        ,
        // TCMSFieldVarchar
        /** @var string - Search term */
        private string $name = '',
        // TCMSFieldNumber
        /** @var int - Number of results */
        private int $numberOfResults = 0,
        // TCMSFieldDateTime
        /** @var DateTime|null - Search date */
        private ?DateTime $searchDate = null,
        // TCMSFieldLookupParentID
        /** @var DataExtranetUser|null - Executed by */
        private ?DataExtranetUser $dataExtranetUser = null
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


    // TCMSFieldExtendedLookup
    public function getCmsLanguage(): ?CmsLanguage
    {
        return $this->cmsLanguage;
    }

    public function setCmsLanguage(?CmsLanguage $cmsLanguage): self
    {
        $this->cmsLanguage = $cmsLanguage;

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


    // TCMSFieldNumber
    public function getNumberOfResults(): int
    {
        return $this->numberOfResults;
    }

    public function setNumberOfResults(int $numberOfResults): self
    {
        $this->numberOfResults = $numberOfResults;

        return $this;
    }


    // TCMSFieldDateTime
    public function getSearchDate(): ?DateTime
    {
        return $this->searchDate;
    }

    public function setSearchDate(?DateTime $searchDate): self
    {
        $this->searchDate = $searchDate;

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


}
