<?php

namespace ChameleonSystem\EcommerceStatsBundle\DataModel;

class StatsTableDataModel
{
    /**
     * @var StatsGroupDataModel[]
     */
    private $blocks;

    /**
     * @var string[]
     */
    private $columnNames;

    /**
     * @var bool
     */
    private $showDiffColumn;

    /**
     * @var int
     */
    private $maxGroupCount;

    /**
     * @param StatsGroupDataModel[] $blocks
     * @param string[]              $columnNames
     */
    public function __construct(array $blocks, array $columnNames, bool $showDiffColumn, int $maxGroupCount)
    {
        $this->blocks = $blocks;
        $this->columnNames = $columnNames;
        $this->showDiffColumn = $showDiffColumn;
        $this->maxGroupCount = $maxGroupCount;
    }

    /**
     * @return StatsGroupDataModel[]
     */
    public function getBlocks(): array
    {
        return $this->blocks;
    }

    /**
     * @return string[]
     */
    public function getColumnNames(): array
    {
        return $this->columnNames;
    }

    public function isShowDiffColumn(): bool
    {
        return $this->showDiffColumn;
    }

    public function getMaxGroupCount(): int
    {
        return $this->maxGroupCount;
    }
}
