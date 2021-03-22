<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
