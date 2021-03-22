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
    public function evaluate(
        \DateTime $startDate,
        \DateTime $endDate,
        string $dateGroupType,
        bool $showDiffColumn,
        string $portalId = ''
    ): StatsTableDataModel;
}
