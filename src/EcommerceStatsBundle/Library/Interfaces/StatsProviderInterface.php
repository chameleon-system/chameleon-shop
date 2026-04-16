<?php

declare(strict_types=1);

namespace ChameleonSystem\EcommerceStatsBundle\Library\Interfaces;

use ChameleonSystem\EcommerceStatsBundle\Library\DataModel\StatisticEvaluationRequestDataModel;
use ChameleonSystem\EcommerceStatsBundle\Library\DataModel\StatsTableDataModel;

interface StatsProviderInterface
{
    /**
     * Data is grouped by date: Every day produces a new column.
     */
    public const DATE_GROUP_DATE = 'day';

    /**
     * Data is grouped by month: Every month produces a new column.
     */
    public const DATE_GROUP_MONTH = 'month';

    /**
     * Data is grouped by year: Every year produces a new column.
     */
    public const DATE_GROUP_YEAR = 'year';

    /**
     * Data is grouped by week: Every week produces a new column.
     */
    public const DATE_GROUP_WEEK = 'week';

    public function addStatsToTable(
        StatsTableDataModel $statsTable,
        StatisticEvaluationRequestDataModel $statisticEvaluationRequestDataModel
    ): StatsTableDataModel;
}
