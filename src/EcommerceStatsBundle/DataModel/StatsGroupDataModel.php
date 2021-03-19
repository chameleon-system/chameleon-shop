<?php

namespace ChameleonSystem\EcommerceStatsBundle\DataModel;

use JetBrains\PhpStorm\ArrayShape;

/**
 * Main data model used in order to display a group of statistics.
 * Statistic groups are organized in a 'tree' fashion where each group in the tree can have
 * subgroups. This is done in order to support 'drilling' down into more detailed values or
 * getting rough overviews by looking at sums of grouped statistics. Each category can contain
 * multiple values for multiple slices of time (displayed in columns in the backend).
 *
 * Fundamentally, there can be 2 types of statistic groups:
 * - Value holders are the leaves of the tree that have no further statistics in them. As such, they
 *   are the smallest category of statistic that is being tracked. Their `groupTotals` define their
 *   parents.
 * - Parent groups have children groups - their `groupTotals` are defined by the sum of the `groupTotals`
 *   of their children.
 *
 * New data can be added to the group by using the {@link StatsGroupDataModel::addRow} method.
 */
class StatsGroupDataModel
{
    public const GROUP_DEF_SHAPE = [
        'sColumnName' => 'string',
        'dColumnValue' => 'int',
    ];

    /**
     * @var string|null
     */
    protected $groupTitle = null;

    /**
     * @var array<string, int>
     */
    protected $groupTotals = [];

    /**
     * @var StatsGroupDataModel[]
     */
    protected $subGroups = [];

    /**
     * @var string[]
     */
    protected $columnNames = [];

    /**
     * @var string
     */
    public $subGroupColumn = '';

    public function init(string $groupTitle, string $subGroupColumn = ''): void
    {
        $this->groupTitle = $groupTitle;
        $this->subGroupColumn = $subGroupColumn;
        $this->groupTotals = [];
        $this->subGroups = [];
        $this->columnNames = [];
    }

    /**
     * Return all column names used in this and any sub groups.
     *
     * @return string[]
     */
    public function getColumnNames(): array
    {
        $names = $this->columnNames;
        foreach ($this->subGroups as $group) {
            $tmpNames = $group->getColumnNames();
            foreach ($tmpNames as $name) {
                if (false === in_array($name, $names)) {
                    $names[] = $name;
                }
            }
        }

        return $names;
    }

    public function getGroupTitle(): string
    {
        return $this->groupTitle;
    }

    public function getMaxValue(): float
    {
        if (0 === count($this->groupTotals)) {
            return 0;
        }

        return max($this->groupTotals);
    }

    /**
     * Return the max group depth for all sub groups.
     */
    public function getMaxGroupDepth(): int
    {
        $maxDepth = 0;
        if (count($this->subGroups) > 0) {
            foreach ($this->subGroups as $group) {
                $maxDepth = max($group->getMaxGroupDepth() + 1, $maxDepth);
            }
        }

        return $maxDepth;
    }

    /**
     * Returns the number of rows that this group contains.
     */
    public function getRowCount(): int
    {
        $depth = 1;
        if (count($this->subGroups) > 0) {
            foreach ($this->subGroups as $group) {
                $depth += $group->getRowCount();
            }
        }

        return $depth;
    }

    /**
     * Add data to the group structure by adding subgroups of the given names.
     * If one group name is specified, then a single sub group is added. If multiple group names
     * are specified, then values are added in 2 places in the graph:.
     *
     * 1. As nested elements in order to track specific values
     * 2. As neighboring elements in order to track category totals
     *
     * @example
     * // Assuming an empty group, the following 2 calls
     * $root->addRow([ 'foo', 'bar' ], [ 'dColumnName' => '2020-12-21', 'dColumnValue' => 1 ]);
     * $root->addRow([ 'bar', 'baz' ], [ 'dColumnName' => '2020-12-21', 'dColumnValue' => 2 ]);
     *
     * // Will create the following structure:
     * // (root)
     * // ┊┄ foo            groupTotals = [ '2020-12-21' => 3 ]
     * // ┊┄ ┊┄ bar         groupTotals = [ '2020-12-21' => 1 ]
     * // ┊┄ bar            groupTotals = [ '2020-12-21' => 3 ]
     * // ┊┄ ┊┄ baz         groupTotals = [ '2020-12-21' => 2 ]
     * // ┊┄ baz            groupTotals = [ '2020-12-21' => 2 ]
     * //
     * // Notice 2 things above:
     * // 1. The `groupTotals` of `foo` and `bar` are not the sum of their children. Rather they are the sum
     * //    of all children that are also of that group.
     * // 2. The item `baz` was pulled to the root as well and now exists twice: Once in order to track all
     * //    stats of category (foo AND baz) and once to track those of just category baz.
     *
     * @param string[] $groupNames - group name list
     */
    public function addRow(
        array $groupNames,
        #[ArrayShape(self::GROUP_DEF_SHAPE)]
        array $groupDef
    ): void {
        // update total
        $this->updateGroupTotals($groupDef);

        if (0 === count($groupNames)) {
            if (false === in_array($groupDef['sColumnName'], $this->columnNames)) {
                $this->columnNames[] = $groupDef['sColumnName'];
            }
            //$this->AddDataColumn($dataCell); // disabled to keep low footprint
            return;
        }

        while (count($groupNames) > 0) {
            $subGroupName = array_shift($groupNames);
            if (false === array_key_exists($subGroupName, $this->subGroups)) {
                $this->subGroups[$subGroupName] = new self();
                $subGroupColumn = array_search($subGroupName, $groupDef) ?? '';
                $this->subGroups[$subGroupName]->init($subGroupName, $subGroupColumn);
            }

            $this->subGroups[$subGroupName]->addRow($groupNames, $groupDef);
        }
    }

    /**
     * Update the totals for the group.
     */
    protected function updateGroupTotals(
        #[ArrayShape(self::GROUP_DEF_SHAPE)]
        array $groupDef
    ): void {
        $columnName = $groupDef['sColumnName'];
        $columnValue = $groupDef['dColumnValue'];
        if (false === array_key_exists($columnName, $this->groupTotals)) {
            $this->groupTotals[$columnName] = 0;
        }
        $this->groupTotals[$columnName] += $columnValue;
    }

    /**
     * Add a column of data.
     */
    protected function addDataColumn(array $dataRow): void
    {
        $columnName = $dataRow['sColumnName'];
        if (false === in_array($columnName, $this->columnNames)) {
            $this->columnNames[] = $columnName;
        }
    }

    public function getTotals(string $name): float
    {
        return $this->groupTotals[$name] ?? 0;
    }

    /**
     * @return StatsGroupDataModel[]
     */
    public function getSubGroups(): array
    {
        return $this->subGroups;
    }
}
