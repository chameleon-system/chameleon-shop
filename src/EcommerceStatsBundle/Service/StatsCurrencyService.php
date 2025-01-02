<?php

namespace ChameleonSystem\EcommerceStatsBundle\Service;

use ChameleonSystem\EcommerceStatsBundle\Library\DataModel\StatsCurrencyDataModel;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsCurrencyServiceInterface;
use Doctrine\DBAL\Connection;

class StatsCurrencyService implements StatsCurrencyServiceInterface
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * @return StatsCurrencyDataModel[]
     */
    public function getAllCurrencies(): array
    {
        $query = 'SELECT `id`, `iso4217`, `symbol` FROM `pkg_shop_currency`';

        $currencies = $this->connection->fetchAllAssociative($query);

        $currencyModels = [];
        foreach ($currencies as $currency) {
            $currencyModels[] = new StatsCurrencyDataModel($currency['iso4217'], $currency['id'], $currency['symbol']);
        }

        return $currencyModels;
    }

    public function getCurrencyById(string $currencyId): ?StatsCurrencyDataModel
    {
        $query = 'SELECT `id`, `iso4217`, `symbol` FROM `pkg_shop_currency`
                    WHERE `id` = :id';

        $currency = $this->connection->fetchAssociative($query, ['id' => $currencyId]);

        if (false === $currency) {
            return null;
        }

        return new StatsCurrencyDataModel($currency['iso4217'], $currency['id'], $currency['symbol']);
    }
}
