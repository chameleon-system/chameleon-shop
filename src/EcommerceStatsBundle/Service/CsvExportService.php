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

namespace ChameleonSystem\EcommerceStatsBundle\Service;

use ChameleonSystem\EcommerceStatsBundle\Library\DataModel\StatsGroupDataModel;
use ChameleonSystem\EcommerceStatsBundle\Library\DataModel\StatsTableDataModel;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\CsvExportServiceInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CsvExportService implements CsvExportServiceInterface
{
    private const TRANSLATION_TOTAL = 'chameleon_system_ecommerce_stats.total';
    private const TRANSLATION_DELTA = 'chameleon_system_ecommerce_stats.delta';

    private TranslatorInterface $translator;
    private \TCMSLocal $local;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;

        $local = \TCMSLocal::GetActive();
        if ($local instanceof \TCMSLocal) {
            $this->local = $local;
        } else {
            // Empty local
            $this->local = new \TCMSLocal();
        }
    }

    public function getCsvDataFromStatsTable(StatsTableDataModel $statsTable): array
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

    /**
     * @return string[]
     */
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

    /**
     * @param string[] $data
     */
    private function exportBlockCSV(
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

    public function getCsvDataFromTopsellers(array $topsellers): array
    {
        // header
        $data = [[
            $this->translator->trans('chameleon_system_ecommerce_stats.field_article_number'),
            $this->translator->trans('chameleon_system_ecommerce_stats.field_article_name'),
            $this->translator->trans('chameleon_system_ecommerce_stats.field_order_count'),
            $this->translator->trans('chameleon_system_ecommerce_stats.field_value'),
            $this->translator->trans('chameleon_system_ecommerce_stats.field_category'),
        ]];

        foreach ($topsellers as $topseller) {
            $data[] = [
                $topseller->getArticleNumber(),
                $topseller->getName(),
                $this->local->FormatNumber($topseller->getTotalOrdered(), 0),
                $this->local->FormatNumber($topseller->getTotalOrderedValue(), 2),
                $topseller->getCategoryPath(),
            ];
        }

        return $data;
    }
}
