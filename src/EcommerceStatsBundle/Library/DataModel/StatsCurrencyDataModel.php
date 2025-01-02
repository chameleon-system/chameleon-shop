<?php

namespace ChameleonSystem\EcommerceStatsBundle\Library\DataModel;

class StatsCurrencyDataModel
{
    private string $isoCode;
    private string $id;
    private string $symbol;

    public function __construct(
        string $isoCode,
        string $id,
        string $symbol
    ) {
        $this->isoCode = $isoCode;
        $this->id = $id;
        $this->symbol = $symbol;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getIsoCode(): string
    {
        return $this->isoCode;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function setIsoCode(string $isoCode): void
    {
        $this->isoCode = $isoCode;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function setSymbol(string $symbol): void
    {
        $this->symbol = $symbol;
    }
}
