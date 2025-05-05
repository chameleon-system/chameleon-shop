<?php

namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

use ChameleonSystem\ExtranetBundle\Entity\DataExtranetUser;
use ChameleonSystem\ShopBundle\Entity\Product\ShopArticle;

class ShopSuggestArticleLog
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldDateTime
        /** @var \DateTime|null - Created on */
        private ?\DateTime $datecreated = null,
        // TCMSFieldLookupParentID
        /** @var DataExtranetUser|null - Shop customer */
        private ?DataExtranetUser $dataExtranetUser = null,
        // TCMSFieldLookup
        /** @var ShopArticle|null - Product / item */
        private ?ShopArticle $shopArticle = null,
        // TCMSFieldEmail
        /** @var string - From (email) */
        private string $fromEmail = '',
        // TCMSFieldVarchar
        /** @var string - From (name) */
        private string $fromName = '',
        // TCMSFieldEmail
        /** @var string - Feedback recipient (email address) */
        private string $toEmail = '',
        // TCMSFieldVarchar
        /** @var string - To (name) */
        private string $toName = '',
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

    // TCMSFieldDateTime
    public function getDatecreated(): ?\DateTime
    {
        return $this->datecreated;
    }

    public function setDatecreated(?\DateTime $datecreated): self
    {
        $this->datecreated = $datecreated;

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

    // TCMSFieldLookup
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
    public function getFromEmail(): string
    {
        return $this->fromEmail;
    }

    public function setFromEmail(string $fromEmail): self
    {
        $this->fromEmail = $fromEmail;

        return $this;
    }

    // TCMSFieldVarchar
    public function getFromName(): string
    {
        return $this->fromName;
    }

    public function setFromName(string $fromName): self
    {
        $this->fromName = $fromName;

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
