<?php

declare(strict_types=1);

namespace ChameleonSystem\EcommerceStatsBundle\Service;

use ChameleonSystem\EcommerceStatsBundle\DataModel\StatsGroupDataModel;
use ChameleonSystem\EcommerceStatsBundle\DataModel\StatsTableDataModel;
use ChameleonSystem\EcommerceStatsBundle\Interfaces\StatsTableCsvExportServiceInterface;
use Symfony\Component\Translation\TranslatorInterface;
use TCMSLocal;

class StatsTableCsvExportService implements StatsTableCsvExportServiceInterface
{
    private const TRANSLATION_TOTAL = 'chameleon_system_ecommerce_stats.total';
    private const TRANSLATION_DELTA = 'chameleon_system_ecommerce_stats.delta';

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var TCMSLocal
     */
    private $local;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->local = TCMSLocal::GetActive();
    }

    public function getCSVData(StatsTableDataModel $statsTable): array
    {
        $data = [
            $this->getHeaderRow($statsTable),
        ];

        foreach ($statsTable->getBlocks() as $block) {
            $this->exportBlockCSV($data, $statsTable, $block, 1);
            $data[] = [];
        }

        return $data;
    }

    private function getHeaderRow(StatsTableDataModel $statsTable): array
    {
        $row = array_fill(0, $statsTable->getMaxGroupCount(), '');

        foreach ($statsTable->getColumnNames() as $name) {
            $row[] = $name;
            if ($statsTable->isShowDiffColumn()) {
                $row[] = $this->translator->trans(self::TRANSLATION_DELTA);
            }
        }

        return $row;
    }

    protected function exportBlockCSV(
        array &$data,
        StatsTableDataModel $statsTable,
        StatsGroupDataModel $group,
        int $level = 1
    ): void {
        $row = array_fill(0, $level - 1, '');
        $row[] = $group->getGroupTitle();

        $emptyGroups = $statsTable->getMaxGroupCount() - $level;

        for ($i = 0; $i < $emptyGroups; ++$i) {
            $row[] = $this->translator->trans(self::TRANSLATION_TOTAL);
        }

        $lastValue = 0;
        foreach ($statsTable->getColumnNames() as $name) {
            $newValue = $group->getTotals($name);
            $row[] = $this->local->FormatNumber($newValue, 2);

            if ($statsTable->isShowDiffColumn()) {
                $diff = $newValue - $lastValue;
                $row[] = $this->local->FormatNumber($diff, 2);
            }

            $lastValue = $newValue;
        }

        $data[] = $row;

        foreach ($group->getSubGroups() as $subGroup) {
            $this->exportBlockCSV($data, $statsTable, $subGroup, $level + 1);
        }
    }
}
