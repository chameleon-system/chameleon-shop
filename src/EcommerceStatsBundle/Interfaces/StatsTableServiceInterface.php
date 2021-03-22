<?php

namespace ChameleonSystem\EcommerceStatsBundle\Interfaces;

use ChameleonSystem\EcommerceStatsBundle\DataModel\StatsTableDataModel;

interface StatsTableServiceInterface
{
    public const DATA_GROUP_TYPE_YEAR = 'year';
    public const DATA_GROUP_TYPE_MONTH = 'month';
    public const DATA_GROUP_TYPE_WEEK = 'week';
    public const DATA_GROUP_TYPE_DAY = 'day';

    /**
     * evaluates the statistics.
     *
     * @param string $dateGroupType one of self::DATA_GROUP_TYPE_*
     */
    public function evaluate(string $startDate, string $endDate, string $dateGroupType, bool $showDiffColumn, string $portalId = ''): StatsTableDataModel;
}
