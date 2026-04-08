<?php

namespace ChameleonSystem\ShopRevocationBundle\DataModel;

class RevocationFormDataModel
{
    public function __construct(
        private string $name = '',
        private string $email = '',
        private string $orderNumber = '',
        private string $shopOrderId = '',
        private string $customerNote = ''
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(string $orderNumber): void
    {
        $this->orderNumber = $orderNumber;
    }

    public function getShopOrderId(): string
    {
        return $this->shopOrderId;
    }

    public function setShopOrderId(string $shopOrderId): void
    {
        $this->shopOrderId = $shopOrderId;
    }

    public function getCustomerNote(): string
    {
        return $this->customerNote;
    }

    public function setCustomerNote(string $customerNote): void
    {
        $this->customerNote = $customerNote;
    }

    /**
     * @return array{name: string, email: string, ordernumber: string, shopOrderId: string, customerNote: string}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'ordernumber' => $this->orderNumber,
            'shopOrderId' => $this->shopOrderId,
            'customerNote' => $this->customerNote,
        ];
    }
}
