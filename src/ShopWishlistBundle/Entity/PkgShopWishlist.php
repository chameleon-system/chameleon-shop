<?php

namespace ChameleonSystem\ShopWishlistBundle\Entity;

use ChameleonSystem\ExtranetBundle\Entity\DataExtranetUser;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class PkgShopWishlist
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var DataExtranetUser|null - Belongs to user */
        private ?DataExtranetUser $dataExtranetUser = null
        ,
        // TCMSFieldText
        /** @var string - Description stored by the user */
        private string $description = '',
        // TCMSFieldBoolean
        /** @var bool - Public */
        private bool $isPublic = false,
        // TCMSFieldPropertyTable
        /** @var Collection<int, PkgShopWishlistArticle> - Wishlist articles */
        private Collection $pkgShopWishlistArticleCollection = new ArrayCollection()
        ,
        // TCMSFieldPropertyTable
        /** @var Collection<int, PkgShopWishlistMailHistory> - Wishlist mail history */
        private Collection $pkgShopWishlistMailHistoryCollection = new ArrayCollection()
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


    // TCMSFieldText
    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }


    // TCMSFieldBoolean
    public function isIsPublic(): bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): self
    {
        $this->isPublic = $isPublic;

        return $this;
    }



    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, PkgShopWishlistArticle>
     */
    public function getPkgShopWishlistArticleCollection(): Collection
    {
        return $this->pkgShopWishlistArticleCollection;
    }

    public function addPkgShopWishlistArticleCollection(PkgShopWishlistArticle $pkgShopWishlistArticle): self
    {
        if (!$this->pkgShopWishlistArticleCollection->contains($pkgShopWishlistArticle)) {
            $this->pkgShopWishlistArticleCollection->add($pkgShopWishlistArticle);
            $pkgShopWishlistArticle->setPkgShopWishlist($this);
        }

        return $this;
    }

    public function removePkgShopWishlistArticleCollection(PkgShopWishlistArticle $pkgShopWishlistArticle): self
    {
        if ($this->pkgShopWishlistArticleCollection->removeElement($pkgShopWishlistArticle)) {
            // set the owning side to null (unless already changed)
            if ($pkgShopWishlistArticle->getPkgShopWishlist() === $this) {
                $pkgShopWishlistArticle->setPkgShopWishlist(null);
            }
        }

        return $this;
    }



    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, PkgShopWishlistMailHistory>
     */
    public function getPkgShopWishlistMailHistoryCollection(): Collection
    {
        return $this->pkgShopWishlistMailHistoryCollection;
    }

    public function addPkgShopWishlistMailHistoryCollection(PkgShopWishlistMailHistory $pkgShopWishlistMailHistory
    ): self {
        if (!$this->pkgShopWishlistMailHistoryCollection->contains($pkgShopWishlistMailHistory)) {
            $this->pkgShopWishlistMailHistoryCollection->add($pkgShopWishlistMailHistory);
            $pkgShopWishlistMailHistory->setPkgShopWishlist($this);
        }

        return $this;
    }

    public function removePkgShopWishlistMailHistoryCollection(PkgShopWishlistMailHistory $pkgShopWishlistMailHistory
    ): self {
        if ($this->pkgShopWishlistMailHistoryCollection->removeElement($pkgShopWishlistMailHistory)) {
            // set the owning side to null (unless already changed)
            if ($pkgShopWishlistMailHistory->getPkgShopWishlist() === $this) {
                $pkgShopWishlistMailHistory->setPkgShopWishlist(null);
            }
        }

        return $this;
    }


}
