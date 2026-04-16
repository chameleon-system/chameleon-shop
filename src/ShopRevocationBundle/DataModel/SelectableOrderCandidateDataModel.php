<?php

namespace ChameleonSystem\ShopRevocationBundle\DataModel;

class SelectableOrderCandidateDataModel
{
    public function __construct(
        private readonly string $id,
        private readonly string $orderNumber,
        private readonly string $dateCreated
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    public function getDateCreated(): string
    {
        return $this->dateCreated;
    }
}
