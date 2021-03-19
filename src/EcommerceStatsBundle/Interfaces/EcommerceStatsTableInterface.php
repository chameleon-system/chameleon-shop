<?php

namespace ChameleonSystem\EcommerceStatsBundle\Interfaces;

use ChameleonSystem\EcommerceStatsBundle\DataModel\TableDataModel;

interface EcommerceStatsTableInterface
{
    const DATA_GROUP_TYPE_YEAR = 'year';
    const DATA_GROUP_TYPE_MONTH = 'month';
    const DATA_GROUP_TYPE_WEEK = 'week';
    const DATA_GROUP_TYPE_DAY = 'day';

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
     * @return strng[] assoc. array
     */
    public function getTableData(): array;

    /**
     * exports csv rows as string array.
     * @return string[][]
     */
    public function getCSVData(): array;
}
