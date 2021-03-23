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
use ChameleonSystem\EcommerceStatsBundle\Interfaces\StatsTableServiceInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\FetchMode;
use Generator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use TdbPkgShopStatisticGroup;
use TdbPkgShopStatisticGroupList;

class StatsTableService implements StatsTableServiceInterface
{
    private const DATE_QUERY_PARTS = [
        self::DATA_GROUP_TYPE_YEAR => 'YEAR(datecreated)',
        self::DATA_GROUP_TYPE_MONTH => "DATE_FORMAT(datecreated,'%Y-%m')",
        self::DATA_GROUP_TYPE_WEEK => "CONCAT(YEAR(datecreated), '-KW', WEEK(datecreated, 7))",
        self::DATA_GROUP_TYPE_DAY => 'DATE(datecreated)',
    ];

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Connection $connection,
        TranslatorInterface $translator,
        LoggerInterface $logger
    ) {
        $this->connection = $connection;
        $this->translator = $translator;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function evaluate(\DateTime $startDate, \DateTime $endDate, string $dateGroupType, bool $showDiffColumn, string $portalId = ''): StatsTableDataModel
    {
        $blocks = [];

        $dateQueryPart = $this->getDateQueryPart($dateGroupType);

        foreach ($this->fetchStatistics() as $group) {
            [ $conditionList, $params ] = $this->getBaseConditions($group, $startDate, $endDate, $portalId);

            $baseQuery = $group->fieldQuery;
            $condition = '';
            if (count($conditionList) > 0) {
                $condition = 'WHERE ('.implode(') AND (', $conditionList).')';
            }

            $blockQuery = str_replace(['[{sColumnName}]', '[{sCondition}]'], [$dateQueryPart, $condition], $baseQuery);
            $groupFields = explode(',', $group->fieldGroups);
            $realGroupFields = array_filter(array_map('trim', $groupFields));

            $this->addBlock($blocks, $group->fieldName, $blockQuery, $realGroupFields, $params);
        }

        return new StatsTableDataModel(
            $blocks,
            $this->getColumnNames($blocks),
            $showDiffColumn,
            $this->getMaxGroupColumnCount($blocks)
        );
    }

    private function getBaseConditions(
        TdbPkgShopStatisticGroup $group,
        \DateTime $startDate,
        \DateTime $endDate,
        string $portalId
    ): array {
        $baseConditionList = [];
        $params = [];

        $baseConditionList[] = $this->connection->quoteIdentifier(str_replace('`', '', $group->fieldDateRestrictionField)).' >= :from';
        $params[':from'] = $startDate->format('Y-m-d H:i:s');

        $baseConditionList[] = $this->connection->quoteIdentifier(str_replace('`', '', $group->fieldDateRestrictionField)).' <= :to';
        $params[':to'] = $endDate->format('Y-m-d H:i:s');

        if ('' !== $group->fieldPortalRestrictionField && '' !== $portalId) {
            $baseConditionList[] = $this->connection->quoteIdentifier(str_replace('`', '', $group->fieldPortalRestrictionField)).' = :portalId';
            $params[':portalId'] = $portalId;
        }

        return [$baseConditionList, $params];
    }

    /**
     * @return Generator<TdbPkgShopStatisticGroup>
     */
    private function fetchStatistics(): Generator
    {
        $groups = TdbPkgShopStatisticGroupList::GetList();
        while ($group = $groups->Next()) {
            yield $group;
        }
    }

    private function getDateQueryPart(string $dateGroupType): string
    {
        return self::DATE_QUERY_PARTS[$dateGroupType]
            ?? self::DATE_QUERY_PARTS[self::DATA_GROUP_TYPE_DAY];
    }

    /**
     * add a new block to the list.
     *
     * @param array<string, StatsGroupDataModel> $blocks
     * @param string[]                           $subGroups
     * @param array<string, string>              $params
     */
    public function addBlock(
        array &$blocks,
        string $blockName,
        string $query,
        array $subGroups = [],
        array $params = []
    ): void {
        if (false === \array_key_exists($blockName, $blocks)) {
            $ecommerceStatsGroup = new StatsGroupDataModel();
            $ecommerceStatsGroup->init($blockName);
            $blocks[$blockName] = $ecommerceStatsGroup;
        }

        foreach ($this->fetchRows($query, $params) as $dataRow) {
            if (!\array_key_exists('sColumnName', $dataRow) || !\array_key_exists('dColumnValue', $dataRow)) {
                $this->logger->error(sprintf(
                    'Could not add block `%s` to table: Query must select at least `sColumnName` and `dColumnValue`',
                    $blockName
                ));

                return;
            }

            $realNames = $this->subGroupsToRealNames($subGroups, $dataRow);
            $blocks[$blockName]->addRow($realNames, $dataRow['sColumnName'], $dataRow['dColumnValue'], $dataRow);
        }
    }

    /**
     * @param array<string, string> $params
     *
     * @return Generator<array>
     */
    private function fetchRows(string $query, array $params): Generator
    {
        try {
            $sqlStatement = $this->connection->executeQuery($query, $params);
        } catch (DBALException $e) {
            $this->logger->error(\sprintf('Error adding ecommerce stats block'), ['exception' => $e]);

            return;
        }

        while ($dataRow = $sqlStatement->fetch(FetchMode::ASSOCIATIVE)) {
            yield $dataRow;
        }
    }

    private function subGroupsToRealNames(array $subGroups, array $dataRow): array
    {
        $realNames = [];
        foreach ($subGroups as $groupName) {
            if (strlen($dataRow[$groupName]) > 0) {
                $realNames[] = $dataRow[$groupName];
            } else {
                $realNames[] = $this->translator->trans('chameleon_system_ecommerce_stats.nothing_assigned');
            }
        }

        return $realNames;
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
