<?php

namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

use ChameleonSystem\DataAccessBundle\Entity\CorePortal\CmsPortal;
use ChameleonSystem\ShopBundle\Entity\Product\ShopArticle;

class PkgShopArticlePreorder
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldExtendedLookup
        /** @var ShopArticle|null - Preordered product */
        private ?ShopArticle $shopArticle = null,
        // TCMSFieldEmail
        /** @var string - Email address */
        private string $preorderUserEmail = '',
        // TCMSFieldDateTime
        /** @var \DateTime|null - Date */
        private ?\DateTime $preorderDate = null,
        // TCMSFieldLookup
        /** @var CmsPortal|null - Belongs to portal */
        private ?CmsPortal $cmsPortal = null
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
    public function getShopArticle(): ?ShopArticle
    {
        return $this->shopArticle;
    }

    public function setShopArticle(?ShopArticle $shopArticle): self
    {
        $this->shopArticle = $shopArticle;

        return $this;
    }

    // TCMSFieldEmail
    public function getPreorderUserEmail(): string
    {
        return $this->preorderUserEmail;
    }

    public function setPreorderUserEmail(string $preorderUserEmail): self
    {
        $this->preorderUserEmail = $preorderUserEmail;

        return $this;
    }

    // TCMSFieldDateTime
    public function getPreorderDate(): ?\DateTime
    {
        return $this->preorderDate;
    }

    public function setPreorderDate(?\DateTime $preorderDate): self
    {
        $this->preorderDate = $preorderDate;

        return $this;
    }

    // TCMSFieldLookup
    public function getCmsPortal(): ?CmsPortal
    {
        return $this->cmsPortal;
    }

    public function setCmsPortal(?CmsPortal $cmsPortal): self
    {
        $this->cmsPortal = $cmsPortal;

        return $this;
    }
}
