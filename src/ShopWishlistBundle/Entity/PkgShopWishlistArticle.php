<?php

namespace ChameleonSystem\ShopWishlistBundle\Entity;

use ChameleonSystem\ShopBundle\Entity\Product\ShopArticle;
use DateTime;

class PkgShopWishlistArticle
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var PkgShopWishlist|null - Belongs to wishlist */
        private ?PkgShopWishlist $pkgShopWishlist = null
        ,
        // TCMSFieldDateTimeNow
        /** @var DateTime|null - Created on */
        private ?DateTime $datecreated = new DateTime(),
        // TCMSFieldNumber
        /** @var int - Amount */
        private int $amount = 0,
        // TCMSFieldExtendedLookup
        /** @var ShopArticle|null - Article */
        private ?ShopArticle $shopArticle = null
        ,
        // TCMSFieldText
        /** @var string - Comment */
        private string $comment = ''
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
    public function getPkgShopWishlist(): ?PkgShopWishlist
    {
        return $this->pkgShopWishlist;
    }

    public function setPkgShopWishlist(?PkgShopWishlist $pkgShopWishlist): self
    {
        $this->pkgShopWishlist = $pkgShopWishlist;

        return $this;
    }


    // TCMSFieldDateTimeNow
    public function getDatecreated(): ?DateTime
    {
        return $this->datecreated;
    }

    public function setDatecreated(?DateTime $datecreated): self
    {
        $this->datecreated = $datecreated;

        return $this;
    }


    // TCMSFieldNumber
    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

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


    // TCMSFieldText
    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }


}
