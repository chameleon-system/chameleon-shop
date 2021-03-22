<?php

declare(strict_types=1);

namespace ChameleonSystem\EcommerceStatsBundle\Interfaces;

use ChameleonSystem\EcommerceStatsBundle\DataModel\StatsTableDataModel;

interface StatsTableCsvExportServiceInterface
{
    /**
     * exports csv rows as string array.
     *
     * @return string[][]
     */
    public function getCSVData(StatsTableDataModel $statsTable): array;
}
