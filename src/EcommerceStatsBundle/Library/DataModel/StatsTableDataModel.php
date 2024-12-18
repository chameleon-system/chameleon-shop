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

namespace ChameleonSystem\EcommerceStatsBundle\Library\DataModel;

class StatsTableDataModel
{
    /**
     * @var array<string, StatsGroupDataModel>
     */
    private array $blocks = [];

    /**
     * @var string[]
     */
    private array $columnNames = [];

    private bool $showDiffColumn = false;
    private int $maxGroupCount = 0;

    /**
     * @return StatsGroupDataModel[]
     */
    public function getBlocks(): array
    {
        return $this->blocks;
    }

    public function getBlock(string $name): ?StatsGroupDataModel
    {
        return $this->blocks[$name] ?? null;
    }

    public function addBlock(string $name, StatsGroupDataModel $block): void
    {
        $this->blocks[$name] = $block;
    }

    /**
     * @return string[]
     */
    public function getColumnNames(): array
    {
        return $this->columnNames;
    }

    /**
     * @param string[] $columnName
     */
    public function setColumnNames(array $columnName): void
    {
        $this->columnNames = $columnName;
    }

    public function isShowDiffColumn(): bool
    {
        return $this->showDiffColumn;
    }

    public function setShowDiffColumn(bool $showDiffColumn): void
    {
        $this->showDiffColumn = $showDiffColumn;
    }

    public function getMaxGroupCount(): int
    {
        return $this->maxGroupCount;
    }

    public function setMaxGroupCount(int $maxGroupCount): void
    {
        $this->maxGroupCount = $maxGroupCount;
    }
}
