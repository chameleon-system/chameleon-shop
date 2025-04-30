<?php

namespace ChameleonSystem\SearchBundle\Entity;

use ChameleonSystem\DataAccessBundle\Entity\Core\CmsLanguage;
use ChameleonSystem\ShopBundle\Entity\Product\ShopArticle;
use ChameleonSystem\ShopBundle\Entity\ShopCore\Shop;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ShopSearchKeywordArticle
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var Shop|null - Belongs to shop */
        private ?Shop $shop = null,
        // TCMSFieldExtendedLookup
        /** @var CmsLanguage|null - Language */
        private ?CmsLanguage $cmsLanguage = null,
        // TCMSFieldVarchar
        /** @var string - Keyword */
        private string $name = '',
        // TCMSFieldLookupMultiselect
        /** @var Collection<int, ShopArticle> - Articles */
        private Collection $shopArticleCollection = new ArrayCollection()
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

    // TCMSFieldLookupMultiselect

    /**
     * @return Collection<int, ShopArticle>
     */
    public function getShopArticleCollection(): Collection
    {
        return $this->shopArticleCollection;
    }

    public function addShopArticleCollection(ShopArticle $shopArticleMlt): self
    {
        if (!$this->shopArticleCollection->contains($shopArticleMlt)) {
            $this->shopArticleCollection->add($shopArticleMlt);
            $shopArticleMlt->set($this);
        }

        return $this;
    }

    public function removeShopArticleCollection(ShopArticle $shopArticleMlt): self
    {
        if ($this->shopArticleCollection->removeElement($shopArticleMlt)) {
            // set the owning side to null (unless already changed)
            if ($shopArticleMlt->get() === $this) {
                $shopArticleMlt->set(null);
            }
        }

        return $this;
    }
}
