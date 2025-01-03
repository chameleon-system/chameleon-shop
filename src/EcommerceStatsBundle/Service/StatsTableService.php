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

namespace ChameleonSystem\EcommerceStatsBundle\Service;

use ChameleonSystem\EcommerceStatsBundle\Library\DataModel\StatsGroupDataModel;
use ChameleonSystem\EcommerceStatsBundle\Library\DataModel\StatsTableDataModel;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsProviderInterface;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsTableServiceInterface;
use ChameleonSystem\EcommerceStatsBundle\StatsProvider\StatsProviderCollection;

class StatsTableService implements StatsTableServiceInterface
{
    /**
     * @var StatsProviderInterface[]
     */
    private array $statsProviders;

    public function __construct(StatsProviderCollection $statsProviderCollection)
    {
        $this->statsProviders = $statsProviderCollection->getProviders();
    }

    /**
     * {@inheritdoc}
     */
    public function evaluate(
        \DateTime $startDate,
        \DateTime $endDate,
        string $dateGroupType,
        bool $showDiffColumn,
        string $portalId = '',
        string $currencyId = '',
        string $selectedStatsGroup = ''): StatsTableDataModel
    {
        $statsTable = new StatsTableDataModel();
        $statsTable->setShowDiffColumn($showDiffColumn);

        foreach ($this->statsProviders as $provider) {
            $statsTable = $provider->addStatsToTable($statsTable, $startDate, $endDate, $dateGroupType, $portalId, $currencyId, $selectedStatsGroup);
        }

        $blocks = $statsTable->getBlocks();
        $statsTable->setColumnNames($this->getColumnNames($blocks));
        $statsTable->setMaxGroupCount($this->getMaxGroupColumnCount($blocks));

        return $statsTable;
    }

    /**
     * @param array<string, StatsGroupDataModel> $blocks
     *
     * @return string[]
     */
    private function getColumnNames(array $blocks): array
    {
        $nameColumns = [];

        foreach ($blocks as $block) {
            $tmpNames = $block->getColumnNames();
            foreach ($tmpNames as $name) {
                if (false === \in_array($name, $nameColumns, true)) {
                    $nameColumns[] = $name;
                }
            }
        }
        asort($nameColumns);

        return $nameColumns;
    }

    /**
     * @param array<string, StatsGroupDataModel> $blocks
     */
    private function getMaxGroupColumnCount(array $blocks): int
    {
        $maxCount = 0;
        foreach ($blocks as $block) {
            $maxCount = max($block->getMaxGroupDepth() + 1, $maxCount);
        }

        return $maxCount;
    }
}
