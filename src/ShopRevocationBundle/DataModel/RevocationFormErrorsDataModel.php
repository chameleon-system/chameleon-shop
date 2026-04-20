<?php

namespace ChameleonSystem\ShopRevocationBundle\DataModel;

class RevocationFormErrorsDataModel
{
    public function __construct(
        private ?string $firstName = null,
        private ?string $lastName = null,
        private ?string $email = null,
        private ?string $orderNumber = null,
        private ?string $shopOrderId = null,
        private ?string $customerNote = null,
        private ?string $legalConsent = null,
        private ?string $general = null
    ) {
    }

    public function getName(): ?string
    {
        return $this->firstName;
    }

    public function setName(?string $name): void
    {
        $this->firstName = $name;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getOrderNumber(): ?string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(?string $orderNumber): void
    {
        $this->orderNumber = $orderNumber;
    }

    public function getShopOrderId(): ?string
    {
        return $this->shopOrderId;
    }

    public function setShopOrderId(?string $shopOrderId): void
    {
        $this->shopOrderId = $shopOrderId;
    }

    public function getCustomerNote(): ?string
    {
        return $this->customerNote;
    }

    public function setCustomerNote(?string $customerNote): void
    {
        $this->customerNote = $customerNote;
    }

    public function getLegalConsent(): ?string
    {
        return $this->legalConsent;
    }

    public function setLegalConsent(?string $legalConsent): void
    {
        $this->legalConsent = $legalConsent;
    }

    public function getGeneral(): ?string
    {
        return $this->general;
    }

    public function setGeneral(?string $general): void
    {
        $this->general = $general;
    }

    public function hasErrors(): bool
    {
        foreach ($this->toArray() as $error) {
            if (null !== $error && '' !== $error) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{firstName: ?string, lastName: ?string, name: ?string, email: ?string, ordernumber: ?string, shopOrderId: ?string, customerNote: ?string, legalConsent: ?string, general: ?string}
     */
    public function toArray(): array
    {
        return [
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'name' => $this->firstName,
            'email' => $this->email,
            'ordernumber' => $this->orderNumber,
            'shopOrderId' => $this->shopOrderId,
            'customerNote' => $this->customerNote,
            'legalConsent' => $this->legalConsent,
            'general' => $this->general,
        ];
    }
}
