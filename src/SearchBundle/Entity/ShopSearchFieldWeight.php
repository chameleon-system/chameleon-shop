<?php

namespace ChameleonSystem\SearchBundle\Entity;

use ChameleonSystem\DataAccessBundle\Entity\Core\CmsLanguage;
use ChameleonSystem\ShopBundle\Entity\ShopCore\Shop;

class ShopSearchFieldWeight
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
        /** @var string - Descriptive name of the field / table combination */
        private string $name = '',
        // TCMSFieldVarchar
        /** @var string - Table */
        private string $tablename = '',
        // TCMSFieldVarchar
        /** @var string - Field */
        private string $fieldname = '',
        // TCMSFieldDecimal
        /** @var string - Weight */
        private string $weight = '',
        // TCMSFieldLookup
        /** @var ShopSearchQuery|null - Selection to be used */
        private ?ShopSearchQuery $shopSearchQuery = null
        ,
        // TCMSFieldVarchar
        /** @var string - Field name in query */
        private string $fieldNameInQuery = '',
        // TCMSFieldBoolean
        /** @var bool - Indexing partial words */
        private bool $indexPartialWords = true
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


    // TCMSFieldVarchar
    public function getTablename(): string
    {
        return $this->tablename;
    }

    public function setTablename(string $tablename): self
    {
        $this->tablename = $tablename;

        return $this;
    }


    // TCMSFieldVarchar
    public function getFieldname(): string
    {
        return $this->fieldname;
    }

    public function setFieldname(string $fieldname): self
    {
        $this->fieldname = $fieldname;

        return $this;
    }


    // TCMSFieldDecimal
    public function getWeight(): string
    {
        return $this->weight;
    }

    public function setWeight(string $weight): self
    {
        $this->weight = $weight;

        return $this;
    }


    // TCMSFieldLookup
    public function getShopSearchQuery(): ?ShopSearchQuery
    {
        return $this->shopSearchQuery;
    }

    public function setShopSearchQuery(?ShopSearchQuery $shopSearchQuery): self
    {
        $this->shopSearchQuery = $shopSearchQuery;

        return $this;
    }


    // TCMSFieldVarchar
    public function getFieldNameInQuery(): string
    {
        return $this->fieldNameInQuery;
    }

    public function setFieldNameInQuery(string $fieldNameInQuery): self
    {
        $this->fieldNameInQuery = $fieldNameInQuery;

        return $this;
    }


    // TCMSFieldBoolean
    public function isIndexPartialWords(): bool
    {
        return $this->indexPartialWords;
    }

    public function setIndexPartialWords(bool $indexPartialWords): self
    {
        $this->indexPartialWords = $indexPartialWords;

        return $this;
    }


}
