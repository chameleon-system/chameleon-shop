<?php

namespace ChameleonSystem\ShopRevocationBundle\DataModel;

class SelectableOrderDataModel
{
    public function __construct(
        private readonly string $name,
        private readonly string $value
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return array{sValue: string, sName: string}
     */
    public function toArray(): array
    {
        return [
            'sValue' => $this->value,
            'sName' => $this->name,
        ];
    }
}
