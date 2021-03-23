<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\EcommerceStatsBundle\Service;

use ChameleonSystem\EcommerceStatsBundle\DataModel\StatsGroupDataModel;
use ChameleonSystem\EcommerceStatsBundle\DataModel\StatsTableDataModel;
use ChameleonSystem\EcommerceStatsBundle\Interfaces\StatsProviderInterface;
use ChameleonSystem\EcommerceStatsBundle\Interfaces\StatsTableServiceInterface;
use ChameleonSystem\EcommerceStatsBundle\StatsProvider\StatsProviderCollection;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use TdbPkgShopStatisticGroup;
use TdbPkgShopStatisticGroupList;

class StatsTableService implements StatsTableServiceInterface
{
    /**
     * @var StatsProviderInterface[]
     */
    private $statsProviders;

    public function __construct(StatsProviderCollection $statsProviderCollection) {
        $this->statsProviders = $statsProviderCollection->getProviders();
    }

    /**
     * {@inheritdoc}
     */
    public function evaluate(\DateTime $startDate, \DateTime $endDate, string $dateGroupType, bool $showDiffColumn, string $portalId = ''): StatsTableDataModel
    {
        $statsTable = new StatsTableDataModel();
        $statsTable->setShowDiffColumn($showDiffColumn);

        foreach ($this->statsProviders as $provider) {
            $statsTable = $provider->addStatsToTable($statsTable, $startDate, $endDate, $dateGroupType, $portalId);
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
