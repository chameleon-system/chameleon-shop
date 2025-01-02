<?php

declare(strict_types=1);

namespace ChameleonSystem\EcommerceStatsBundle\Library\Interfaces;

use ChameleonSystem\EcommerceStatsBundle\Library\DataModel\StatsTableDataModel;

interface StatsProviderInterface
{
    /**
     * Data is grouped by date: Every day produces a new column.
     */
    public const DATA_GROUP_TYPE_DAY = 'day';

    /**
     * Data is grouped by month: Every month produces a new column.
     */
    public const DATA_GROUP_TYPE_MONTH = 'month';

    /**
     * Data is grouped by year: Every year produces a new column.
     */
    public const DATA_GROUP_TYPE_YEAR = 'year';

    /**
     * Data is grouped by week: Every week produces a new column.
     */
    public const DATA_GROUP_TYPE_WEEK = 'week';

    /**
     * Adds statistics for the given range to the table.
     * The `$dateGroupType` will be one of the `DATE_GROUP_*` constants
     * and should influence the columns added.
     */
    public function addStatsToTable(
        StatsTableDataModel $statsTable,
        \DateTime $startDate,
        \DateTime $endDate,
        string $dateGroupType,
        string $portalId,
        string $currencyId
    ): StatsTableDataModel;
}
