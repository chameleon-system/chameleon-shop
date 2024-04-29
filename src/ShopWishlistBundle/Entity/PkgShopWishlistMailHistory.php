<?php

namespace ChameleonSystem\ShopWishlistBundle\Entity;

use DateTime;

class PkgShopWishlistMailHistory
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var PkgShopWishlist|null - Belongs to wishlist */
        private ?PkgShopWishlist $pkgShopWishlist = null
        ,
        // TCMSFieldDateTime
        /** @var DateTime|null - Email sent on */
        private ?DateTime $datesend = null,
        // TCMSFieldVarchar
        /** @var string - Recipient name */
        private string $toName = '',
        // TCMSFieldEmail
        /** @var string - Feedback recipient (Email address) */
        private string $toEmail = '',
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


    // TCMSFieldDateTime
    public function getDatesend(): ?DateTime
    {
        return $this->datesend;
    }

    public function setDatesend(?DateTime $datesend): self
    {
        $this->datesend = $datesend;

        return $this;
    }


    // TCMSFieldVarchar
    public function getToName(): string
    {
        return $this->toName;
    }

    public function setToName(string $toName): self
    {
        $this->toName = $toName;

        return $this;
    }


    // TCMSFieldEmail
    public function getToEmail(): string
    {
        return $this->toEmail;
    }

    public function setToEmail(string $toEmail): self
    {
        $this->toEmail = $toEmail;

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
