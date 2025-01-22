<?php

namespace ChameleonSystem\EcommerceStatsBundle\Bridge\Chameleon\Dashboard\Widgets;

use ChameleonSystem\CmsDashboardBundle\Bridge\Chameleon\Attribute\ExposeAsApi;
use ChameleonSystem\CmsDashboardBundle\Bridge\Chameleon\Dashboard\Widgets\DashboardWidget;
use ChameleonSystem\CmsDashboardBundle\Bridge\Chameleon\Service\DashboardCacheService;
use ChameleonSystem\CmsDashboardBundle\DataModel\WidgetDropdownItemDataModel;
use ChameleonSystem\CmsDashboardBundle\Library\Interfaces\ColorGeneratorServiceInterface;
use ChameleonSystem\EcommerceStatsBundle\Bridge\Chameleon\BackendModule\EcommerceStatsBackendModule;
use ChameleonSystem\EcommerceStatsBundle\Library\DataModel\DashboardTimeframeDataModel;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsCurrencyServiceInterface;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsTableServiceInterface;
use ChameleonSystem\SecurityBundle\Service\SecurityHelperAccess;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class DashboardBaseWidget extends DashboardWidget
{
    private array $statsCache = [];

    public function __construct(
        protected readonly DashboardCacheService $dashboardCacheService,
        protected readonly \ViewRenderer $renderer,
        protected readonly StatsTableServiceInterface $stats,
        protected readonly TranslatorInterface $translator,
        protected readonly StatsCurrencyServiceInterface $statsCurrencyService,
        protected readonly string $defaultTimeframe,
        protected readonly ColorGeneratorServiceInterface $colorGeneratorService,
        protected readonly SecurityHelperAccess $securityHelperAccess
    ) {
        parent::__construct($dashboardCacheService, $translator);
    }

    public function getTitle(): string
    {
        return $this->getStatsGroup($this->getStatsGroupSystemName())?->getGroupTitle() ?? '';
    }

    public function showWidget(): bool
    {
        return $this->securityHelperAccess->isGranted(EcommerceStatsBackendModule::CMS_RIGHT_ECOMMERCE_STATS_SHOW_MODULE);
    }

    public function getDropdownItems(): array
    {
        $button = new WidgetDropdownItemDataModel(
            'reload'.$this->getChartId(),
            $this->translator->trans('chameleon_system_ecommerce_stats.widgets.reload_button_label'),
            ''
        );

        $button->addDataAttribute('data-service-alias', $this->getChartId());
        $button->addDataAttribute('data-reload-chart', 'reload'); // just a dummy for the event listener

        return [$button];
    }

    protected function getStatsGroupSystemName(): string
    {
        return '';
    }

    protected function generateBodyHtml(): string
    {
        $this->renderer->AddSourceObject('statsData', $this->getStatsDataAsArray());
        $this->renderer->AddSourceObject('chartId', str_replace('-', '', $this->getChartId()));

        $renderedStatistic = $this->renderer->Render('@ChameleonSystemEcommerceStats/snippets-cms/ecommerceStats/module/dashboard-barchart.html.twig');

        return "<div>
                    <div class='bg-white'>
                        ".$renderedStatistic.'
                    </div>
                </div>';
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

    #[ExposeAsApi(description: 'Call this method dynamically via API:/cms/api/dashboard/widget/{widgetServiceId}/getStatsDataAsJson')]
    public function getStatsDataAsJson(): JsonResponse
    {
        $this->getBodyHtml(true); // trigger widget rendering to update the cache
        $dataset = $this->getStatsDataAsArray();

        return new JsonResponse($dataset);
    }

    private function getStatsDataAsArray(): array
    {
        $statsGroup = $this->getStatsGroup($this->getStatsGroupSystemName());

        $groupElements = [];
        $labels = [];
        $elementCount = \count($statsGroup->getSubGroups());

        if (1 > $elementCount) {
            // Single group case
            $backgroundColors = [];
            $colorIndex = 0;
            foreach ($statsGroup->getGroupTotals() as $timeframe => $value) {
                $labels[] = $timeframe;
                $backgroundColors[] = $this->colorGeneratorService->generateColor($colorIndex, $elementCount);
                $colorIndex++;
            }

            $groupElements[] = [
                'label' => $statsGroup->getGroupTitle(),
                'backgroundColor' => $backgroundColors, // color array
                'data' => array_values($statsGroup->getGroupTotals()),
            ];
        } else {
            // Sub-Groups case
            foreach ($statsGroup->getSubGroups() as $subGroupName => $subGroup) {
                if (empty($labels)) {
                    // Add labels from the first subgroup
                    foreach ($subGroup->getGroupTotals() as $timeframe => $value) {
                        $labels[] = $timeframe;
                    }
                }

                $groupElements[] = [
                    'label' => $subGroupName,
                    'backgroundColor' => $this->colorGeneratorService->generateColor(count($groupElements), $elementCount),
                    'data' => array_values($subGroup->getGroupTotals()),
                ];
            }
        }

        return [
            'labels' => $labels,
            'datasets' => $groupElements,
            'dateTime' => date('d.m.Y H:i'),
            'hasCurrency' => $statsGroup->hasCurrency(),
            'currency' => $statsGroup->getCurrency() ? $statsGroup->getCurrency()->getSymbol() : '€',
        ];
    }

    public function getFooterIncludes(): array
    {
        $includes = parent::getFooterIncludes();
        $includes[] = '<script type="text/javascript" src="/bundles/chameleonsystemecommercestats/ecommerce_stats/js/chart.4.4.7.js"></script>';
        $includes[] = '<script type="text/javascript" src="/bundles/chameleonsystemecommercestats/ecommerce_stats/js/chart-init.4.4.7.js"></script>';
        $includes[] = '<script type="text/javascript" src="/bundles/chameleonsystemecommercestats/ecommerce_stats/js/dashboard.js"></script>';

        return $includes;
    }

    protected function getTimeframe(): DashboardTimeframeDataModel
    {
        $endDate = new \DateTime('now');

        $startDate = clone $endDate;
        $startDate->modify($this->defaultTimeframe);

        return new DashboardTimeframeDataModel(
            $startDate,
            $endDate
        );
    }
}
