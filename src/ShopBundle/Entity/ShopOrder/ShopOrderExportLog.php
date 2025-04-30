<?php

namespace ChameleonSystem\ShopBundle\Entity\ShopOrder;

class ShopOrderExportLog
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var ShopOrder|null - Belongs to order */
        private ?ShopOrder $shopOrder = null,
        // TCMSFieldDateTimeNow
        /** @var \DateTime|null - Created on */
        private ?\DateTime $datecreated = new \DateTime(),
        // TCMSFieldVarchar
        /** @var string - IP */
        private string $ip = '',
        // TCMSFieldText
        /** @var string - Data */
        private string $data = '',
        // TCMSFieldVarchar
        /** @var string - Session ID */
        private string $userSessionId = ''
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
    public function getShopOrder(): ?ShopOrder
    {
        return $this->shopOrder;
    }

    public function setShopOrder(?ShopOrder $shopOrder): self
    {
        $this->shopOrder = $shopOrder;

        return $this;
    }

    // TCMSFieldDateTimeNow
    public function getDatecreated(): ?\DateTime
    {
        return $this->datecreated;
    }

    public function setDatecreated(?\DateTime $datecreated): self
    {
        $this->datecreated = $datecreated;

        return $this;
    }

    // TCMSFieldVarchar
    public function getIp(): string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    // TCMSFieldText
    public function getData(): string
    {
        return $this->data;
    }

    public function setData(string $data): self
    {
        $this->data = $data;

        return $this;
    }

    // TCMSFieldVarchar
    public function getUserSessionId(): string
    {
        return $this->userSessionId;
    }

    public function setUserSessionId(string $userSessionId): self
    {
        $this->userSessionId = $userSessionId;

        return $this;
    }
}
