<?php

namespace ChameleonSystem\ShopBundle\Entity\Product;

class ShopStockMessageTrigger
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var ShopStockMessage|null - Stock message */
        private ?ShopStockMessage $shopStockMessage = null,
        // TCMSFieldNumber
        /** @var int - Amount */
        private int $amount = 0,
        // TCMSFieldVarchar
        /** @var string - Message */
        private string $message = '',
        // TCMSFieldVarchar
        /** @var string - System name */
        private string $systemName = '',
        // TCMSFieldVarchar
        /** @var string - CSS class */
        private string $cssClass = ''
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
    public function getShopStockMessage(): ?ShopStockMessage
    {
        return $this->shopStockMessage;
    }

    public function setShopStockMessage(?ShopStockMessage $shopStockMessage): self
    {
        $this->shopStockMessage = $shopStockMessage;

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

    // TCMSFieldVarchar
    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    // TCMSFieldVarchar
    public function getSystemName(): string
    {
        return $this->systemName;
    }

    public function setSystemName(string $systemName): self
    {
        $this->systemName = $systemName;

        return $this;
    }

    // TCMSFieldVarchar
    public function getCssClass(): string
    {
        return $this->cssClass;
    }

    public function setCssClass(string $cssClass): self
    {
        $this->cssClass = $cssClass;

        return $this;
    }
}
