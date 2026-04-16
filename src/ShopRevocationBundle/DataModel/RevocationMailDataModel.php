<?php

namespace ChameleonSystem\ShopRevocationBundle\DataModel;

class RevocationMailDataModel
{
    public function __construct(
        private readonly string $revocationRecordId,
        private readonly string $orderId,
        private readonly string $orderNumber,
        private readonly string $orderDate,
        private readonly string $customerName,
        private readonly string $customerEmail,
        private readonly string $customerNote,
        private readonly \DateTimeImmutable $submittedAt
    ) {
    }

    public function getRevocationRecordId(): string
    {
        return $this->revocationRecordId;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    public function getCustomerName(): string
    {
        return $this->customerName;
    }

    public function getCustomerEmail(): string
    {
        return $this->customerEmail;
    }

    /**
     * @return array{customerName: string, customerMail: string, ordernumber: string, orderDate: string, customerText: string, revocationDate: string, revocationTime: string}
     */
    public function getMailData(): array
    {
        return [
            'customerName' => $this->customerName,
            'customerMail' => $this->customerEmail,
            'ordernumber' => $this->orderNumber,
            'orderDate' => $this->orderDate,
            'customerText' => $this->customerNote,
            'revocationDate' => $this->submittedAt->format('d.m.Y'),
            'revocationTime' => $this->submittedAt->format('H:i:s'),
        ];
    }
}
