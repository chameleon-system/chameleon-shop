<?php

declare(strict_types=1);

namespace ChameleonSystem\EcommerceStatsBundle\StatsProvider;

use ChameleonSystem\EcommerceStatsBundle\Bridge\Chameleon\BackendModule\EcommerceStatsBackendModule;
use ChameleonSystem\EcommerceStatsBundle\Library\DataModel\StatisticEvaluationRequestDataModel;
use ChameleonSystem\EcommerceStatsBundle\Library\DataModel\StatsGroupDataModel;
use ChameleonSystem\EcommerceStatsBundle\Library\DataModel\StatsTableDataModel;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsCurrencyServiceInterface;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsProviderInterface;
use ChameleonSystem\SecurityBundle\Service\SecurityHelperAccess;
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
    private const array DATE_QUERY_PARTS = [
        self::DATE_GROUP_YEAR => 'YEAR(%1$s)',
        self::DATE_GROUP_MONTH => 'DATE_FORMAT(%1$s,\'%%Y-%%m\')',
        self::DATE_GROUP_WEEK => 'DATE_FORMAT(%1$s,\'%%x-KW%%v\')',
        self::DATE_GROUP_DATE => 'DATE(%1$s)',
    ];

    private const string DEFAULT_DATE_RESTRICTION_FIELD = 'datecreated';

    public function __construct(
        private readonly Connection $connection,
        private readonly LoggerInterface $logger,
        private readonly TranslatorInterface $translator,
        private readonly StatsCurrencyServiceInterface $currencyService,
        private readonly SecurityHelperAccess $securityHelperAccess
    ) {
    }

    public function addStatsToTable(
        StatsTableDataModel $statsTable,
        StatisticEvaluationRequestDataModel $statisticEvaluationRequestDataModel
    ): StatsTableDataModel {
        foreach ($this->fetchStatistics($statisticEvaluationRequestDataModel->getSelectedStatsGroupSystemName()) as $group) {
            [$conditionList, $params] = $this->getBaseConditions(
                $group,
                $statisticEvaluationRequestDataModel
            );

            $condition = '';
            if (count($conditionList) > 0) {
                $condition = 'WHERE ('.implode(') AND (', $conditionList).')';
            }

            $dateQueryPart = sprintf(
                self::DATE_QUERY_PARTS[$statisticEvaluationRequestDataModel->getDateGroup()] ?? self::DATE_QUERY_PARTS[self::DATE_GROUP_DATE],
                $group->fieldDateRestrictionField ?? self::DEFAULT_DATE_RESTRICTION_FIELD
            );

            $blockQuery = str_replace(
                ['[{sColumnName}]', '[{sCondition}]'],
                [$dateQueryPart, $condition],
                $group->fieldQuery
            );

            $blockQuery = $this->replaceTranslatableFields($blockQuery);
            $groupFields = explode(',', $group->fieldGroups);
            $realGroupFields = array_filter(array_map('trim', $groupFields));

            $statsTable = $this->addBlock(
                $statsTable,
                $group->fieldName,
                $group->fieldSystemName,
                $group->fieldHasCurrency,
                $blockQuery,
                $realGroupFields,
                $params,
                $statisticEvaluationRequestDataModel->getCurrencyId()
            );
        }

        return $statsTable;
    }

    private function replaceTranslatableFields(string $query): string
    {
        return preg_replace_callback('/<trans>(.*?)<\/trans>/i', function ($matches) {
            $content = $matches[1];
            $activeBackendLanguage = $this->securityHelperAccess->getUser()?->getCmsLanguageId();
            $langKey = \TGlobal::GetLanguagePrefix($activeBackendLanguage);
            if('' === $langKey){
                $langKey = $this->securityHelperAccess->getUser()?->getCurrentEditLanguageIsoCode();
            }

            // attempt to decode JSON content inside <trans> tag
            $decoded = json_decode($content, true);
            if (true === is_array($decoded)) {
                // handle explicit language mapping like {"de": "...", "en": "...", "default": "en"}
                if (isset($decoded[$langKey])) {
                    return $decoded[$langKey];
                }

                // en, as the default language, has no language prefix
                if ('' === $langKey && isset($decoded['en'])) {
                    return $decoded['en'];
                }

                if (isset($decoded['default']) && isset($decoded[$decoded['default']])) {
                    return $decoded[$decoded['default']];
                }

                // fallback: return the first available value
                return reset($decoded);
            }

            // if not JSON, treat it as a field name (e.g., table.field) and append suffix
            if ('' === $langKey) {
                return $content;
            }

            // remove backticks from the content if present
            $content = str_replace('`', '', $content);
            $result = $content.'__'.$langKey;

            // re-add backticks around the result, add backticks before and after any '.'
            $result = '`'.str_replace('.', '`.`', $result).'`';

            return $result;
        }, $query);
    }

    /**
     * @return \Generator<\TdbPkgShopStatisticGroup>
     */
    private function fetchStatistics(string $selectedStatsGroupSystemName): \Generator
    {
        $query = null;
        if (
            EcommerceStatsBackendModule::ALL_STATS_FILTER_NAME !== $selectedStatsGroupSystemName
            && '' !== $selectedStatsGroupSystemName
        ) {
            $query = 'SELECT * FROM `pkg_shop_statistic_group` WHERE `pkg_shop_statistic_group`.`system_name` = '
                .$this->connection->quote($selectedStatsGroupSystemName);
        }
        $groups = \TdbPkgShopStatisticGroupList::GetList($query);

        while ($group = $groups->Next()) {
            yield $group;
        }
    }

    /**
     * @return (string[]|array<string>)[] - first item is condition strings,
     *                                    second item is parameters required for them
     */
    private function getBaseConditions(
        \TdbPkgShopStatisticGroup $group,
        StatisticEvaluationRequestDataModel $statisticEvaluationRequestDataModel
    ): array {
        $baseConditionList = [];
        $params = [];

        $baseConditionList[] = $this->connection->quoteIdentifier(
            str_replace('`', '', $group->fieldDateRestrictionField)
        ).' >= :from';
        $params[':from'] = $statisticEvaluationRequestDataModel->getStartDate()->format('Y-m-d H:i:s');

        $baseConditionList[] = $this->connection->quoteIdentifier(
            str_replace('`', '', $group->fieldDateRestrictionField)
        ).' <= :to';
        $params[':to'] = $statisticEvaluationRequestDataModel->getEndDate()->format('Y-m-d H:i:s');

        if (true === $group->fieldHasCurrency) {
            $baseConditionList[] = $this->connection->quoteIdentifier('shop_order.pkg_shop_currency_id').' = :currencyId';
            $params[':currencyId'] = $statisticEvaluationRequestDataModel->getCurrencyId();
        }

        if ('' !== $group->fieldPortalRestrictionField && '' !== $statisticEvaluationRequestDataModel->getPortalId()) {
            $baseConditionList[] = $this->connection->quoteIdentifier(
                str_replace('`', '', $group->fieldPortalRestrictionField)
            ).' = :portalId';
            $params[':portalId'] = $statisticEvaluationRequestDataModel->getPortalId();
        }

        if ('' !== $statisticEvaluationRequestDataModel->getShopId()) {
            $baseConditionList[] = $this->connection->quoteIdentifier('shop_order.shop_id').' = :shopId';
            $params[':shopId'] = $statisticEvaluationRequestDataModel->getShopId();
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
