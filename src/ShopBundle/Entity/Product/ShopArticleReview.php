<?php

namespace ChameleonSystem\ShopBundle\Entity\Product;

use ChameleonSystem\ExtranetBundle\Entity\DataExtranetUser;
use DateTime;

class ShopArticleReview
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var ShopArticle|null - Belongs to product */
        private ?ShopArticle $shopArticle = null
        ,
        // TCMSFieldLookupParentID
        /** @var DataExtranetUser|null - Written by */
        private ?DataExtranetUser $dataExtranetUser = null
        ,
        // TCMSFieldBoolean
        /** @var bool - Published */
        private bool $publish = false,
        // TCMSFieldVarchar
        /** @var string - Author */
        private string $authorName = '',
        // TCMSFieldVarchar
        /** @var string - Review title */
        private string $title = '',
        // TCMSFieldEmail
        /** @var string - Author's email address */
        private string $authorEmail = '',
        // TCMSFieldBoolean
        /** @var bool - Send comment notification to the author */
        private bool $sendCommentNotification = false,
        // TCMSFieldNumber
        /** @var int - Rating */
        private int $rating = 0,
        // TCMSFieldNumber
        /** @var int - Helpful review */
        private int $helpfulCount = 0,
        // TCMSFieldNumber
        /** @var int - Review is not helpful */
        private int $notHelpfulCount = 0,
        // TCMSFieldVarchar
        /** @var string - Action ID */
        private string $actionId = '',
        // TCMSFieldText
        /** @var string - Review */
        private string $comment = '',
        // TCMSFieldDateTime
        /** @var DateTime|null - Created on */
        private ?DateTime $datecreated = null,
        // TCMSFieldVarchar
        /** @var string - IP address */
        private string $userIp = ''
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


    // TCMSFieldBoolean
    public function isPublish(): bool
    {
        return $this->publish;
    }

    public function setPublish(bool $publish): self
    {
        $this->publish = $publish;

        return $this;
    }


    // TCMSFieldVarchar
    public function getAuthorName(): string
    {
        return $this->authorName;
    }

    public function setAuthorName(string $authorName): self
    {
        $this->authorName = $authorName;

        return $this;
    }


    // TCMSFieldVarchar
    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }


    // TCMSFieldEmail
    public function getAuthorEmail(): string
    {
        return $this->authorEmail;
    }

    public function setAuthorEmail(string $authorEmail): self
    {
        $this->authorEmail = $authorEmail;

        return $this;
    }


    // TCMSFieldBoolean
    public function isSendCommentNotification(): bool
    {
        return $this->sendCommentNotification;
    }

    public function setSendCommentNotification(bool $sendCommentNotification): self
    {
        $this->sendCommentNotification = $sendCommentNotification;

        return $this;
    }


    // TCMSFieldNumber
    public function getRating(): int
    {
        return $this->rating;
    }

    public function setRating(int $rating): self
    {
        $this->rating = $rating;

        return $this;
    }


    // TCMSFieldNumber
    public function getHelpfulCount(): int
    {
        return $this->helpfulCount;
    }

    public function setHelpfulCount(int $helpfulCount): self
    {
        $this->helpfulCount = $helpfulCount;

        return $this;
    }


    // TCMSFieldNumber
    public function getNotHelpfulCount(): int
    {
        return $this->notHelpfulCount;
    }

    public function setNotHelpfulCount(int $notHelpfulCount): self
    {
        $this->notHelpfulCount = $notHelpfulCount;

        return $this;
    }


    // TCMSFieldVarchar
    public function getActionId(): string
    {
        return $this->actionId;
    }

    public function setActionId(string $actionId): self
    {
        $this->actionId = $actionId;

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


    // TCMSFieldDateTime
    public function getDatecreated(): ?DateTime
    {
        return $this->datecreated;
    }

    public function setDatecreated(?DateTime $datecreated): self
    {
        $this->datecreated = $datecreated;

        return $this;
    }


    // TCMSFieldVarchar
    public function getUserIp(): string
    {
        return $this->userIp;
    }

    public function setUserIp(string $userIp): self
    {
        $this->userIp = $userIp;

        return $this;
    }


}
