<?php

namespace ChameleonSystem\EcommerceStatsBundle\Service;

class EcommerceStatsGroup
{

    /**
     * @var string|null
     */
    protected $groupTitle = null;

    /**
     * @var array<string, int>
     */
    protected $groupTotals = [];

    /**
     * @var EcommerceStatsGroup[]
     */
    protected $subGroups = [];

    /**
     * @var string[]
     */
    protected $columnNames = [];

    /**
     * @var array<string, mixed>|null
     */
    protected $groupData = null;

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
     * Return all column names used in this and any sub groups
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

    public function getGroupName(): string
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
     * Return the max group depth for all sub groups
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
     *
     * @return int
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
     * @return array<string, mixed>
     */
    public function evaluateGroupData(array $columnNames, ?int $maxGroupDepth = null, $showDiffColumn = false, $rowPrefix = '', $separator = ';'): array
    {
        if (null === $this->groupData) {
            $subGroups = $this->subGroups;

            foreach ($subGroups as $subGroup) {
                $subGroup->evaluateGroupData($columnNames, $maxGroupDepth, $showDiffColumn, $rowPrefix, $separator);
            }

            $this->groupData = [
                'group' => $this,
                'groupTitle' => $this->groupTitle,
                'groupTotals' => $this->groupTotals,
                'subGroups' => $subGroups,
                'columnNames' => $columnNames,
                'maxGroupDepth' => $maxGroupDepth,
                'maxValue' => $this->getMaxValue(),
                'rowCount' => $this->getRowCount(),
            ];
        }

        return $this->groupData;
    }

    /**
     * Add data to the group structure
     * @param array $subGroupDef - group name list
     * @param array $dataCell
     */
    public function addRow(array $subGroupDef, array $dataCell): void
    {
        // update total
        $this->updateGroupTotals($dataCell);

        if (0 === count($subGroupDef)) {
            if (false === in_array($dataCell['sColumnName'], $this->columnNames)) {
                $this->columnNames[] = $dataCell['sColumnName'];
            }
            //$this->AddDataColumn($dataCell); // disabled to keep low footprint
        } else {
            while (count($subGroupDef) > 0) {
                $subGroupName = array_shift($subGroupDef);
                if (false === array_key_exists($subGroupName, $this->subGroups)) {
                    $this->subGroups[$subGroupName] = new self();
                    $subGroupColumn = array_search($subGroupName, $dataCell) ?? '';
                    $this->subGroups[$subGroupName]->init($subGroupName, $subGroupColumn);
                }

                $this->subGroups[$subGroupName]->addRow($subGroupDef, $dataCell);
            }
        }
    }

    /**
     * Update the totals for the group.
     *
     * @param array $dataRow
     */
    protected function updateGroupTotals(array $dataRow): void
    {
        $columnName = $dataRow['sColumnName'];
        $columnValue = $dataRow['dColumnValue'];
        if (false === array_key_exists($columnName, $this->groupTotals)) {
            $this->groupTotals[$columnName] = 0;
        }
        $this->groupTotals[$columnName] += $columnValue;
    }

    /**
     * Add a column of data.
     *
     * @param array $dataRow
     */
    protected function addDataColumn(array $dataRow): void
    {
        $columnName = $dataRow['sColumnName'];
        if (false === in_array($columnName, $this->columnNames)) {
            $this->columnNames[] = $columnName;
        }
    }

    public function getGroupData(): ?array
    {
        return $this->groupData;
    }

    public function getTotals(string $name): float
    {
        return $this->groupTotals[$name] ?? 0;
    }

    /**
     * @return EcommerceStatsGroup[]
     */
    public function getSubGroups(): array
    {
        return $this->subGroups;
    }
}
