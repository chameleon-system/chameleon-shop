<?php

namespace ChameleonSystem\EcommerceStatsBundle\Interfaces;

use ChameleonSystem\EcommerceStatsBundle\DataModel\StatsTableDataModel;
use ChameleonSystem\EcommerceStatsBundle\DataModel\TableDataModel;

interface StatsTableServiceInterface
{
    public const DATA_GROUP_TYPE_YEAR = 'year';
    public const DATA_GROUP_TYPE_MONTH = 'month';
    public const DATA_GROUP_TYPE_WEEK = 'week';
    public const DATA_GROUP_TYPE_DAY = 'day';

    /**
     * evaluates the statistics.
     * @param string $startDate
     * @param string $endDate
     * @param string $dateGroupType one of self::DATA_GROUP_TYPE_*
     * @param bool $showDiffColumn
     * @param string $portalId
     */
    public function evaluate(string $startDate, string $endDate, string $dateGroupType, bool $showDiffColumn, string $portalId = ''): void;

    /**
     * structure of the evaluated table.
     */
    public function getTableData(): StatsTableDataModel;

    /**
     * exports csv rows as string array.
     * @return string[][]
     */
    public function getCSVData(): array;
}
