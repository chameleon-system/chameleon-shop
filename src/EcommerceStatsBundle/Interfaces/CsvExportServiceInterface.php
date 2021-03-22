<?php

namespace ChameleonSystem\EcommerceStatsBundle\Interfaces;

use ChameleonSystem\EcommerceStatsBundle\DataModel\ShopOrderItemDataModel;
use ChameleonSystem\EcommerceStatsBundle\DataModel\StatsTableDataModel;

interface CsvExportServiceInterface
{
    /**
     * @return string[][]
     */
    public function getCsvDataFromStatsTable(StatsTableDataModel $statsTable): array;

    /**
     * @param ShopOrderItemDataModel[] $topsellers
     * @return string[][]
     */
    public function getCsvDataFromTopsellers(array $topsellers): array;
}
