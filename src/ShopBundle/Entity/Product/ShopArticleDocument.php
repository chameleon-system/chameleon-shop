<?php

namespace ChameleonSystem\ShopBundle\Entity\Product;

use ChameleonSystem\DataAccessBundle\Entity\CoreMedia\CmsDocument;

class ShopArticleDocument
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var ShopArticle|null - Belongs to article */
        private ?ShopArticle $shopArticle = null,
        // TCMSFieldLookup
        /** @var ShopArticleDocumentType|null - Article document type */
        private ?ShopArticleDocumentType $shopArticleDocumentType = null,
        // TCMSFieldExtendedLookup
        /** @var CmsDocument|null - Document */
        private ?CmsDocument $cmsDocument = null,
        // TCMSFieldPosition
        /** @var int - Position */
        private int $position = 0
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

    // TCMSFieldLookup
    public function getShopArticleDocumentType(): ?ShopArticleDocumentType
    {
        return $this->shopArticleDocumentType;
    }

    public function setShopArticleDocumentType(?ShopArticleDocumentType $shopArticleDocumentType): self
    {
        $this->shopArticleDocumentType = $shopArticleDocumentType;

        return $this;
    }

    // TCMSFieldExtendedLookup
    public function getCmsDocument(): ?CmsDocument
    {
        return $this->cmsDocument;
    }

    public function setCmsDocument(?CmsDocument $cmsDocument): self
    {
        $this->cmsDocument = $cmsDocument;

        return $this;
    }

    // TCMSFieldPosition
    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }
}
