<?php

namespace ChameleonSystem\ShopRevocationBundle\DataModel;

class RevocationFormDataModel
{
    public function __construct(
        private string $firstName = '',
        private string $lastName = '',
        private string $email = '',
        private string $orderNumber = '',
        private string $shopOrderId = '',
        private string $customerNote = '',
        private bool $legalConsent = false
    ) {
    }

    public function getName(): string
    {
        return $this->firstName.' '.$this->lastName;
    }

    public function setName(string $name): void
    {
        $nameParts = preg_split('/\s+/', trim($name), 2);
        if (false === $nameParts) {
            $this->firstName = trim($name);
            $this->lastName = '';

            return;
        }

        $this->firstName = trim((string) ($nameParts[0] ?? ''));
        $this->lastName = trim((string) ($nameParts[1] ?? ''));
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
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

    public function hasLegalConsent(): bool
    {
        return $this->legalConsent;
    }

    public function setLegalConsent(bool $legalConsent): void
    {
        $this->legalConsent = $legalConsent;
    }

    /**
     * @return array{firstName: string, lastName: string, name: string, email: string, ordernumber: string, shopOrderId: string, customerNote: string, legalConsent: bool}
     */
    public function toArray(): array
    {
        return [
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'name' => $this->getName(),
            'email' => $this->email,
            'ordernumber' => $this->orderNumber,
            'shopOrderId' => $this->shopOrderId,
            'customerNote' => $this->customerNote,
            'legalConsent' => $this->legalConsent,
        ];
    }
}
