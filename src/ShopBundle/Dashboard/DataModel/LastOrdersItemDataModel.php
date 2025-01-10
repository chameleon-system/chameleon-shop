<?php

namespace ChameleonSystem\ShopBundle\Dashboard\DataModel;

class LastOrdersItemDataModel
{
    private string $recordId = '';
    private string $orderNumber = '';
    private string $orderDate = '';
    private string $customerName = ''; // combination from firstname and lastname and company
    private string $customerEmail = '';
    private string $customerCity = '';
    private string $customerCountryCode = '';
    private string $paymentMethod = '';
    private float $orderValue = 0.0;
    private string $orderCurrencyCode = '';
    private string $orderCurrencySymbol = '';
    private int $orderItemCount = 0;
    private float $discountValue = 0;
    private bool $paymentSuccessful = false;
    private bool $orderCanceled = false;
    private bool $isGuestOrder = false;
    private string $detailUrl = '';

    public function getRecordId(): string
    {
        return $this->recordId;
    }

    public function setRecordId(string $recordId): void
    {
        $this->recordId = $recordId;
    }

    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(string $orderNumber): void
    {
        $this->orderNumber = $orderNumber;
    }

    public function getOrderDate(): string
    {
        return $this->orderDate;
    }

    public function setOrderDate(string $orderDate): void
    {
        $this->orderDate = $orderDate;
    }

    public function getCustomerName(): string
    {
        return $this->customerName;
    }

    public function setCustomerName(string $customerName): void
    {
        $this->customerName = $customerName;
    }

    public function getCustomerEmail(): string
    {
        return $this->customerEmail;
    }

    public function setCustomerEmail(string $customerEmail): void
    {
        $this->customerEmail = $customerEmail;
    }

    public function getCustomerCity(): string
    {
        return $this->customerCity;
    }

    public function setCustomerCity(string $customerCity): void
    {
        $this->customerCity = $customerCity;
    }

    public function getCustomerCountryCode(): string
    {
        return $this->customerCountryCode;
    }

    public function setCustomerCountryCode(string $customerCountryCode): void
    {
        $this->customerCountryCode = $customerCountryCode;
    }

    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(string $paymentMethod): void
    {
        $this->paymentMethod = $paymentMethod;
    }

    public function getOrderValue(): float
    {
        return $this->orderValue;
    }

    public function setOrderValue(float $orderValue): void
    {
        $this->orderValue = $orderValue;
    }

    public function getOrderItemCount(): int
    {
        return $this->orderItemCount;
    }

    public function setOrderItemCount(int $orderItemCount): void
    {
        $this->orderItemCount = $orderItemCount;
    }

    public function getOrderCurrencyCode(): string
    {
        return $this->orderCurrencyCode;
    }

    public function setOrderCurrencyCode(string $orderCurrencyCode): void
    {
        $this->orderCurrencyCode = $orderCurrencyCode;
    }

    public function getDiscountValue(): float
    {
        return $this->discountValue;
    }

    public function setDiscountValue(float $discountValue): void
    {
        $this->discountValue = $discountValue;
    }

    public function isPaymentSuccessful(): bool
    {
        return $this->paymentSuccessful;
    }

    public function setPaymentSuccessful(bool $paymentSuccessful): void
    {
        $this->paymentSuccessful = $paymentSuccessful;
    }

    public function isOrderCanceled(): bool
    {
        return $this->orderCanceled;
    }

    public function setOrderCanceled(bool $orderCanceled): void
    {
        $this->orderCanceled = $orderCanceled;
    }

    public function getOrderCurrencySymbol(): string
    {
        return $this->orderCurrencySymbol;
    }

    public function setOrderCurrencySymbol(string $orderCurrencySymbol): void
    {
        $this->orderCurrencySymbol = $orderCurrencySymbol;
    }

    public function getDetailUrl(): string
    {
        return $this->detailUrl;
    }

    public function setDetailUrl(string $detailUrl): void
    {
        $this->detailUrl = $detailUrl;
    }

    public function isGuestOrder(): bool
    {
        return $this->isGuestOrder;
    }

    public function setIsGuestOrder(bool $isGuestOrder): void
    {
        $this->isGuestOrder = $isGuestOrder;
    }
}
