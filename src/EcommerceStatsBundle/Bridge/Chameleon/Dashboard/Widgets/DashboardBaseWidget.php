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
        protected readonly ColorGeneratorServiceInterface $colorGeneratorService
    ) {
        parent::__construct($dashboardCacheService, $translator);
    }

    public function getTitle(): string
    {
        return $this->getStatsGroup($this->getStatsGroupSystemName())?->getGroupTitle() ?? '';
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
        $this->renderer->AddSourceObject('group', $this->getStatsGroup($this->getStatsGroupSystemName()));
        $this->renderer->AddSourceObject('chartId', str_replace('-', '', $this->getChartId()));

        $renderedStatistic = $this->renderer->Render('@ChameleonSystemEcommerceStats/snippets-cms/ecommerceStats/module/barchart-body.html.twig');

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

        $statsGroup = $this->getStatsGroup($this->getStatsGroupSystemName());

        $groupElements = [];
        $elementCount = \count($statsGroup->getSubGroups());

        if (1 > $elementCount) {
            $groupElement = [];
            $groupElement['label'] = $statsGroup->getGroupTitle();
            $groupElement['backgroundColor'] = $this->colorGeneratorService->generateColor(0, $elementCount);
            $data = [];
            foreach ($statsGroup->getGroupTotals() as $value) {
                $data[] = $value;
            }

            $groupElement['data'] = $data;
            $groupElements[] = $groupElement;
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
                $groupElements[] = $groupElement;
                ++$index;
            }
        }

        $dataset = [
            'datasets' => $groupElements,
            'dateTime' => date('d.m.Y H:i'),
        ];

        return new JsonResponse(json_encode($dataset));
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
