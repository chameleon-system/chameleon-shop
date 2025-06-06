<?php

declare(strict_types=1);

namespace ChameleonSystem\EcommerceStatsBundle\StatsProvider;

use ChameleonSystem\CmsBackendBundle\BackendSession\BackendSessionInterface;
use ChameleonSystem\CoreBundle\ServiceLocator;
use ChameleonSystem\EcommerceStatsBundle\Bridge\Chameleon\BackendModule\EcommerceStatsBackendModule;
use ChameleonSystem\EcommerceStatsBundle\Library\DataModel\StatsGroupDataModel;
use ChameleonSystem\EcommerceStatsBundle\Library\DataModel\StatsTableDataModel;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsCurrencyServiceInterface;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsProviderInterface;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Provides statistics based on statistics groups that can be configured in
 * the backend. Every statistics defines a query that is used in order to fetch
 * the statistics from the database.
 *
 * Please refer to the helptext of the columns in the `pkg_shop_statistics_group`
 * table for more information.
 */
class PkgShopStatisticsGroupProvider implements StatsProviderInterface
{
    private const DATE_QUERY_PARTS = [
        self::DATA_GROUP_TYPE_YEAR => 'YEAR(%1$s)',
        self::DATA_GROUP_TYPE_MONTH => 'DATE_FORMAT(%1$s,\'%%Y-%%m\')',
        self::DATA_GROUP_TYPE_WEEK => 'CONCAT(YEAR(%1$s), \'-KW\', WEEK(%1$s, 7))',
        self::DATA_GROUP_TYPE_DAY => 'DATE(%1$s)',
    ];

    private Connection $connection;
    private LoggerInterface $logger;
    private TranslatorInterface $translator;

    public function __construct(
        Connection $connection,
        LoggerInterface $logger,
        TranslatorInterface $translator,
        private readonly StatsCurrencyServiceInterface $currencyService,
        private readonly BackendSessionInterface $backendSession
    ) {
        $this->connection = $connection;
        $this->logger = $logger;
        $this->translator = $translator;
    }

    public function addStatsToTable(
        StatsTableDataModel $statsTable,
        \DateTime $startDate,
        \DateTime $endDate,
        string $dateGroupType,
        string $portalId,
        string $currencyId,
        string $selectedStatsGroupSystemName
    ): StatsTableDataModel {
        foreach ($this->fetchStatistics($selectedStatsGroupSystemName) as $group) {
            [$conditionList, $params] = $this->getBaseConditions($group, $startDate, $endDate, $portalId);
            $dateQueryPart = $this->getDateQueryPart($dateGroupType, $group->fieldDateRestrictionField ?? 'datecreated');

            $baseQuery = $group->fieldQuery;
            $condition = '';
            if (count($conditionList) > 0) {
                $condition = 'WHERE ('.implode(') AND (', $conditionList).')';

                if (true === $group->fieldHasCurrency) {
                    $condition .= ' AND (`shop_order`.`pkg_shop_currency_id` ='."'".$currencyId."')";
                }
            }

            $blockQuery = str_replace(['[{sColumnName}]', '[{sCondition}]'], [$dateQueryPart, $condition], $baseQuery);
            $blockQuery = $this->replaceTranslatableFields($blockQuery);
            $groupFields = explode(',', $group->fieldGroups);
            $realGroupFields = array_filter(array_map('trim', $groupFields));

            $statsTable = $this->addBlock($statsTable, $group->fieldName, $group->fieldSystemName, $group->fieldHasCurrency, $blockQuery, $realGroupFields, $params, $currencyId);
        }

        return $statsTable;
    }

    private function replaceTranslatableFields(string $query): string
    {
        return preg_replace_callback('/<trans>([a-z0-9_]+\.[a-z0-9_]+)<\/trans>/i', function ($matches) {
            $field = $matches[1];
            $activeBackendLanguage = $this->backendSession->getCurrentEditLanguageId();
            $suffix = \TGlobal::GetLanguagePrefix($activeBackendLanguage);
            if ('' === $suffix) {
                // If no language prefix is set, return the field as is
                return $field;
            }
            return $field.'__'. $suffix;
        }, $query);
    }

    public function fetchAllStatisticGroupsNames(): array
    {
        $groupNames = [];
        $groupList = \TdbPkgShopStatisticGroupList::GetList();
        while ($group = $groupList->Next()) {
            $groupNames[$group->fieldSystemName] = $group->fieldName;
        }

        return $groupNames;
    }

    /**
     * @return \Generator<\TdbPkgShopStatisticGroup>
     */
    private function fetchStatistics(string $selectedStatsGroupSystemName): \Generator
    {
        if (EcommerceStatsBackendModule::ALL_STATS_FILTER_NAME === $selectedStatsGroupSystemName || '' === $selectedStatsGroupSystemName) {
            $groups = \TdbPkgShopStatisticGroupList::GetList();
        } else {
            $query = 'SELECT * FROM `pkg_shop_statistic_group` WHERE `pkg_shop_statistic_group`.`system_name` = '."'$selectedStatsGroupSystemName'";

            $groups = \TdbPkgShopStatisticGroupList::GetList($query);
        }

        while ($group = $groups->Next()) {
            yield $group;
        }
    }

    private function getDateQueryPart(string $dateGroupType, string $dateColumn): string
    {
        $part = self::DATE_QUERY_PARTS[$dateGroupType]
            ?? self::DATE_QUERY_PARTS[self::DATA_GROUP_TYPE_DAY];

        return sprintf($part, $dateColumn);
    }

    /**
     * @return (string[]|array<string>)[] - first item is condition strings,
     *                                    second item is parameters required for them
     */
    private function getBaseConditions(
        \TdbPkgShopStatisticGroup $group,
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
     * add a new block to the list.
     *
     * @param string[] $subGroups
     * @param array<string, string> $params
     */
    private function addBlock(
        StatsTableDataModel $statsTable,
        string $blockTitle,
        string $blockSystemName,
        bool $hasCurrency,
        string $query,
        array $subGroups = [],
        array $params = [],
        string $currencyId = ''
    ): StatsTableDataModel {
        $block = $statsTable->getBlock($blockSystemName);
        if (null === $block) {
            $block = new StatsGroupDataModel($blockTitle, $blockSystemName);
            $block->setHasCurrency($hasCurrency);
            $block->setCurrency($this->currencyService->getCurrencyById($currencyId));
            $statsTable->addBlock($blockSystemName, $block);
        }

        $rows = $this->fetchRows($query, $params);
        foreach ($rows as $dataRow) {
            if (!\array_key_exists('sColumnName', $dataRow) || !\array_key_exists('dColumnValue', $dataRow)) {
                $this->logger->error(sprintf(
                    'Could not add block `%s` to table: Query must select at least `sColumnName` and `dColumnValue`',
                    $blockSystemName
                ));

                return $statsTable;
            }

            $realNames = $this->subGroupsToRealNames($subGroups, $dataRow);
            $block->addRow(
                $realNames,
                (string) $dataRow['sColumnName'],
                (float) $dataRow['dColumnValue'],
                $dataRow
            );
        }

        return $statsTable;
    }

    /**
     * @param array<string, string> $params
     *
     * @return \Generator<array>
     */
    private function fetchRows(string $query, array $params): \Generator
    {
        try {
            $sqlStatement = $this->connection->executeQuery($query, $params);
        } catch (\Throwable $e) {
            $this->logger->error(sprintf('Error adding ecommerce stats block'), ['exception' => $e]);

            return;
        }

        while ($dataRow = $sqlStatement->fetchAssociative()) {
            yield $dataRow;
        }
    }

    /**
     * @param string[] $subGroups
     * @param array<string, mixed> $dataRow
     *
     * @return string[]
     */
    private function subGroupsToRealNames(array $subGroups, array $dataRow): array
    {
        $realNames = [];
        foreach ($subGroups as $groupName) {
            if (isset($dataRow[$groupName]) && '' !== trim($dataRow[$groupName])) {
                // Use the actual value if it's not empty
                $realNames[] = $dataRow[$groupName];
            } else {
                // Use translation for 'not_assigned'
                $realNames[] = $this->translator->trans('chameleon_system_ecommerce_stats.nothing_assigned');
            }
        }

        return $realNames;
    }
}
