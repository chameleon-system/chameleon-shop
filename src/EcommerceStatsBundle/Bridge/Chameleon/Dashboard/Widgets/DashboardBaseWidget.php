<?php

namespace ChameleonSystem\EcommerceStatsBundle\Bridge\Chameleon\Dashboard\Widgets;

use ChameleonSystem\CmsDashboardBundle\Bridge\Chameleon\Dashboard\Widgets\DashboardWidget;
use ChameleonSystem\CmsDashboardBundle\Bridge\Chameleon\Attribute\ExposeAsApi;
use ChameleonSystem\CmsDashboardBundle\Library\Interfaces\ColorGeneratorServiceInterface;
use ChameleonSystem\EcommerceStatsBundle\Bridge\Chameleon\BackendModule\EcommerceStatsBackendModule;
use ChameleonSystem\EcommerceStatsBundle\Library\DataModel\DashboardTimeframeDataModel;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsCurrencyServiceInterface;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsTableServiceInterface;
use DateTime;
use esono\pkgCmsCache\CacheInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class DashboardBaseWidget extends DashboardWidget
{
    private array $statsCache = [];

    public function __construct(
        protected readonly CacheInterface $cache,
        protected readonly \ViewRenderer $renderer,
        protected readonly StatsTableServiceInterface $stats,
        protected readonly TranslatorInterface $translator,
        protected readonly StatsCurrencyServiceInterface $statsCurrencyService,
        protected readonly String $defaultTimeframe,
        protected readonly ColorGeneratorServiceInterface $colorGeneratorService
    )
    {
        parent::__construct($cache);
    }

    public function getTitle(): string
    {
        return '';
    }

    public function getDropdownItems(): array
    {
        return [];
    }

    protected function generateBodyHtml(): string
    {
        return '';
    }

    protected function getChartId(): string
    {
        return '';
    }

    protected function getStatsGroup(string $statsSystemName)
    {
        if (array_key_exists($statsSystemName, $this->statsCache)) {
            return $this->statsCache[$statsSystemName];
        }

        $timespan = $this->getTimeframe();

        $statistic = $this->stats->evaluate(
            $timespan->getStartDate(),
            $timespan->getEndDate(),
            'day',
            false,
            '',
            $this->statsCurrencyService->getCurrencyIdByIsoCode(EcommerceStatsBackendModule::STANDARD_CURRENCY_ISO_CODE),
            $statsSystemName
        );

        $statisticBlocks = $statistic->getBlocks();

        $this->statsCache[$statsSystemName] = $statisticBlocks[$statsSystemName] ?? null;

        return $this->statsCache[$statsSystemName];
    }

    #[ExposeAsApi(description: "Call this method dynamically via API:/cms/api/dashboard/widget/{widgetServiceId}/getStatsDataAsJson")]
    public function getStatsDataAsJson(): JsonResponse
    {
        $statsGroup = $this->getStatsGroup($this->getStatsSystemName());

        $goupElements = [];
        $elementCount = \count($statsGroup->getSubGroups());

        if (1 > $elementCount) {
            $groupElement = [];
            $groupElement['label'] = $statsGroup->getGroupTitle();
            $groupElement['backgroundColor'] = $this->colorGeneratorService->generateColor(0, $elementCount);
            $data = [];
            foreach($statsGroup->getGroupTotals() as $value) {
                $data[] = $value;
            }

            $groupElement['data'] = $data;
            $goupElements[] = $groupElement;
        } else {
            $index = 0;
            foreach ($statsGroup->getSubGroups() as $subGroupName => $subGroup) {
                $groupElement = [];
                $groupElement['label'] = $subGroupName;
                $groupElement['backgroundColor'] = $this->colorGeneratorService->generateColor($index, $elementCount);
                $data = [];
                foreach ($subGroup->getGroupTotals() as $value) {
                    $data[] = $value;
                }
                $groupElement['data'] = $data;
                $goupElements[] = $groupElement;
                $index++;
            }
        }

        $dataset = [
            $goupElements
        ];

        $encoded = json_encode($dataset);
        return new JsonResponse($encoded);
    }

    protected function getStatsSystemName(): string
    {
        return '';
    }

    public function getFooterIncludes(): array
    {
        $includes = parent::getFooterIncludes();
        $includes[] = '<script type="text/javascript" src="/bundles/chameleonsystemecommercestats/ecommerce_stats/js/chart.4.4.7.js"></script>';
        $includes[] = '<script type="text/javascript" src="/bundles/chameleonsystemecommercestats/ecommerce_stats/js/chart-init.4.4.7.js"></script>';

        return $includes;
    }

    protected function getTimeframe(): DashboardTimeframeDataModel
    {
        $endDate = new DateTime('now');

        $startDate = clone $endDate;
        $startDate->modify($this->defaultTimeframe);

        return new DashboardTimeframeDataModel(
            $startDate,
            $endDate
        );
    }
}
