<?php

namespace ChameleonSystem\ShopRatingServiceBundle\Entity;

class PkgShopRatingServiceRating
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldLookup
        /** @var PkgShopRatingService|null - Rating service */
        private ?PkgShopRatingService $pkgShopRatingService = null,
        // TCMSFieldVarchar
        /** @var string - Remote key */
        private string $remoteKey = '',
        // TCMSFieldDecimal
        /** @var string - Rating */
        private string $score = '',
        // TCMSFieldText
        /** @var string - Raw data */
        private string $rawdata = '',
        // TCMSFieldVarchar
        /** @var string - User who rates */
        private string $ratingUser = '',
        // TCMSFieldText
        /** @var string - Rating text */
        private string $ratingText = '',
        // TCMSFieldDateTime
        /** @var \DateTime|null - Date of rating */
        private ?\DateTime $ratingDate = null
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

    // TCMSFieldLookup
    public function getPkgShopRatingService(): ?PkgShopRatingService
    {
        return $this->pkgShopRatingService;
    }

    public function setPkgShopRatingService(?PkgShopRatingService $pkgShopRatingService): self
    {
        $this->pkgShopRatingService = $pkgShopRatingService;

        return $this;
    }

    // TCMSFieldVarchar
    public function getRemoteKey(): string
    {
        return $this->remoteKey;
    }

    public function setRemoteKey(string $remoteKey): self
    {
        $this->remoteKey = $remoteKey;

        return $this;
    }

    // TCMSFieldDecimal
    public function getScore(): string
    {
        return $this->score;
    }

    public function setScore(string $score): self
    {
        $this->score = $score;

        return $this;
    }

    // TCMSFieldText
    public function getRawdata(): string
    {
        return $this->rawdata;
    }

    public function setRawdata(string $rawdata): self
    {
        $this->rawdata = $rawdata;

        return $this;
    }

    // TCMSFieldVarchar
    public function getRatingUser(): string
    {
        return $this->ratingUser;
    }

    public function setRatingUser(string $ratingUser): self
    {
        $this->ratingUser = $ratingUser;

        return $this;
    }

    // TCMSFieldText
    public function getRatingText(): string
    {
        return $this->ratingText;
    }

    public function setRatingText(string $ratingText): self
    {
        $this->ratingText = $ratingText;

        return $this;
    }

    // TCMSFieldDateTime
    public function getRatingDate(): ?\DateTime
    {
        return $this->ratingDate;
    }

    public function setRatingDate(?\DateTime $ratingDate): self
    {
        $this->ratingDate = $ratingDate;

        return $this;
    }
}
