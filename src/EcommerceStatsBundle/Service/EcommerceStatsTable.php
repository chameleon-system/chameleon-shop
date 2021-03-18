<?php

namespace ChameleonSystem\EcommerceStatsBundle\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\FetchMode;
use ChameleonSystem\EcommerceStatsBundle\Interfaces\EcommerceStatsTableInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class EcommerceStatsTable implements EcommerceStatsTableInterface
{
    /**
     * @var EcommerceStatsGroup[]
     */
    private array $blocks = [];

    private bool $showDiffColumn = false;

    private ?string $blockName = null;

    /**
     * @var array string[]
     */
    private array $columnNames;

    private int $maxGroupCount;

    /**
     * @var Connection
     */
    private Connection $connection;

    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    public function __construct(
        Connection $connection, 
        TranslatorInterface $translator, 
        LoggerInterface $logger
    )
    {
        $this->connection = $connection;
        $this->translator = $translator;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function evaluate(string $startDate, string $endDate, string $dateGroupType, bool $showDiffColumn, string $portalId = ''): void
    {
        $this->blocks = [];
        $this->blockName = null;
        $this->dateGroupType = $dateGroupType;
        $this->setShowDiffColumn($showDiffColumn);

        switch ($this->dateGroupType) {
            case self::DATA_GROUP_TYPE_YEAR:
                $dateQueryPart = 'YEAR(datecreated)';
                break;

            case self::DATA_GROUP_TYPE_MONTH:
                $dateQueryPart = "DATE_FORMAT(datecreated,'%Y-%m')";
                break;

            case self::DATA_GROUP_TYPE_WEEK:
                $dateQueryPart = "CONCAT(YEAR(datecreated), '-KW', WEEK(datecreated, 7))";
                break;

            case self::DATA_GROUP_TYPE_DAY:
            default:
                $dateQueryPart = 'DATE(datecreated)';
                break;
        }

        $groups = \TdbPkgShopStatisticGroupList::GetList();
        while ($group = $groups->Next()) {
            $params = [];
            $baseConditionList = [];

            if (null !== $startDate) {
                $baseConditionList[] = $this->connection->quoteIdentifier(str_replace('`', '', $group->fieldDateRestrictionField)).' >= :from';
                $params[':from'] = $startDate;
            }

            if (null !== $endDate) {
                $baseConditionList[] = $this->connection->quoteIdentifier(str_replace('`', '', $group->fieldDateRestrictionField)).' <= :to';
                $params[':to'] = $endDate;
            }

            if ('' !== $group->fieldPortalRestrictionField && '' !== $portalId) {
                $baseConditionList[] = $this->connection->quoteIdentifier(str_replace('`', '', $group->fieldPortalRestrictionField)).' = :portalId';
                $params[':portalId'] = $portalId;
            }

            $conditionList = $baseConditionList;
            $baseQuery = $group->fieldQuery;
            $condition = '';
            if (count($conditionList) > 0) {
                $condition = 'WHERE ('.implode(') AND (', $conditionList).')';
            }
            $blockQuery = str_replace(['[{sColumnName}]', '[{sCondition}]'], [$dateQueryPart, $condition], $baseQuery);
            $groupFields = explode(',', $group->fieldGroups);
            $realGroupFields = array_filter(array_map('trim', $groupFields));

            $this->addBlock($group->fieldName, $blockQuery, $realGroupFields, $params);
        }

        $this->columnNames = $this->getColumnNames();
        $this->maxGroupCount = $this->getMaxGroupColumnCount();
        $this->showDiffColumn = $this->isShowDiffColumn();
    }

    /*
     * add a new block to the list
    */
    protected function addBlock(string $blockName, string $query, array $subGroups = [], array $params = []): void
    {
        try {
            $sqlStatement = $this->connection->executeQuery($query, $params);
        } catch (DBALException $e) {
            $this->logger->error(\sprintf('Error adding ecommerce stats block'), ['exception' => $e]);

            return;
        }

        if (false === array_key_exists($blockName, $this->blocks)) {
            $ecommerceStatsGroup = new EcommerceStatsGroup();
            $ecommerceStatsGroup->init($blockName);
            $this->blocks[$blockName] = $ecommerceStatsGroup;
        }

        while ($dataRow = $sqlStatement->fetch(FetchMode::ASSOCIATIVE)) {
            $realNames = [];
            foreach ($subGroups as $groupName) {
                if (strlen($dataRow[$groupName]) > 0) {
                    $realNames[] = $dataRow[$groupName];
                } else {
                    $realNames[] = $this->translator->trans('chameleon_system_ecommerce_stats.nothing_assigned');
                }
            }
            $this->blocks[$blockName]->addRow($realNames, $dataRow);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTableData(): array
    {
        $columnNames = $this->columnNames;
        $maxGroupCount = $this->maxGroupCount;

        foreach ($this->blocks as $block) {
            $block->evaluateGroupData($columnNames, $maxGroupCount, $this->showDiffColumn, '', ';');
        }

        return [
            'blocks' => $this->blocks,
            'columnNames' => $columnNames,
            'showDiffColumn' => $this->showDiffColumn,
            'maxGroupCount' => $maxGroupCount,
        ];
    }

    public function getColumnNames(): array
    {
        $nameColumns = [];
        foreach ($this->blocks as $block) {
            $tmpNames = $block->getColumnNames();
            foreach ($tmpNames as $name) {
                if (false === in_array($name, $nameColumns)) {
                    $nameColumns[] = $name;
                }
            }
        }
        asort($nameColumns);

        return $nameColumns;
    }

    public function getMaxGroupColumnCount(): int
    {
        $maxCount = 0;
        foreach ($this->blocks as $block) {
            $maxCount = max($block->getMaxGroupDepth() + 1, $maxCount);
        }

        return $maxCount;
    }

    public function setShowDiffColumn(bool $showDiffColumn): void
    {
        $this->showDiffColumn = $showDiffColumn;
    }

    public function isShowDiffColumn(): bool
    {
        return $this->showDiffColumn;
    }

    public function setBlockName(?string $blockName): void
    {
        $this->blockName = $blockName;
    }

    /**
     * @return EcommerceStatsGroup[]
     */
    public function getBlocks(): array
    {
        return $this->blocks;
    }

    public function getCSVData(): array
    {
        $this->local = \TCMSLocal::GetActive();

        $row = array_fill(0, $this->maxGroupCount, '');

        foreach ($this->columnNames as $name) {
            $row[] = $name;
            if ($this->showDiffColumn) {
                $row[] = $this->translator->trans('chameleon_system_ecommerce_stats.delta');
            }
        }

        $data = [$row]; // header

        foreach ($this->blocks as $block) {
            $this->exportBlockCSV($data, $block, 1);
            $data[] = [];
        }

        return $data;
    }

    protected function exportBlockCSV(array &$data, EcommerceStatsGroup $group, int $level = 1): void
    {
        $row = array_fill(0, $level - 1, '');
        $row[] = $group->getGroupName();

        $emptyGroups = $this->maxGroupCount - $level;

        for ($i = 0; $i < $emptyGroups; ++$i) {
            $row[] = $this->translator->trans('chameleon_system_ecommerce_stats.total');
        }

        $oldVal = 0;
        foreach ($this->columnNames as $name) {
            $newVal = $group->getTotals($name) ?? 0;
            $row[] = $this->local->FormatNumber($newVal, 2);
            $dDiff = $newVal - $oldVal;
            if ($this->showDiffColumn) {
                $row[] = $this->local->FormatNumber($dDiff, 2);
            }
            $oldVal = $newVal;
        }

        $data[] = $row;

        foreach ($group->getSubGroups() as $subGroup) {
            $this->exportBlockCSV($data, $subGroup, $level + 1);
        }
    }
}
