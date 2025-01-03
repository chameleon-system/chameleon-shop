<?php

namespace ChameleonSystem\EcommerceStatsBundle\Library\Interfaces;

use ChameleonSystem\EcommerceStatsBundle\Library\DataModel\StatsCurrencyDataModel;

interface StatsCurrencyServiceInterface
{
    public function getAllCurrencies();

    public function getCurrencyById(string $currencyId): ?StatsCurrencyDataModel;

    public function getCurrencyIdByIsoCode(string $isoCode): ?string;
}
