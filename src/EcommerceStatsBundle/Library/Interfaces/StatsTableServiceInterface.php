<?php

declare(strict_types=1);

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\EcommerceStatsBundle\Library\Interfaces;

use ChameleonSystem\EcommerceStatsBundle\Library\DataModel\StatsTableDataModel;

interface StatsTableServiceInterface
{
    /**
     * evaluates the statistics.
     *
     * @param string $dateGroupType one of StatsProviderInterface::DATA_GROUP_TYPE_*
     */
    public function evaluate(
        \DateTime $startDate,
        \DateTime $endDate,
        string $dateGroupType,
        bool $showDiffColumn,
        string $portalId = '',
        string $currencyId = '',
        string $selectedStatsGroup = ''
    ): StatsTableDataModel;
}
