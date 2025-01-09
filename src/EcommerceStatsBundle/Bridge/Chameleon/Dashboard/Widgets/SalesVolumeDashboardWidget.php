<?php

namespace ChameleonSystem\EcommerceStatsBundle\Bridge\Chameleon\Dashboard\Widgets;

use ChameleonSystem\CmsDashboardBundle\DataModel\WidgetDropdownItemDataModel;
use ChameleonSystem\CmsDashboardBundle\Library\Interfaces\ColorGeneratorServiceInterface;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsCurrencyServiceInterface;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsTableServiceInterface;
use esono\pkgCmsCache\CacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SalesVolumeDashboardWidget extends DashboardBaseWidget
{
    private const ORDER_NUMBER_STATISTICS_GROUP_SYSTEM_NAME = 'sales_without_shipping';

    public function __construct(
        CacheInterface $cache,
        \ViewRenderer $viewRenderer,
        StatsTableServiceInterface $statsTable,
        TranslatorInterface $translator,
        StatsCurrencyServiceInterface $currencyService,
        String $defaultTimeframe,
        ColorGeneratorServiceInterface $colorGeneratorService
    ) {
        parent::__construct($cache, $viewRenderer, $statsTable, $translator, $currencyService, $defaultTimeframe, $colorGeneratorService);
    }

    public function getTitle(): string
    {
        return $this->getStatsGroup($this->getStatsSystemName())?->getGroupTitle();
    }

    public function getDropdownItems(): array
    {
        $button = new WidgetDropdownItemDataModel('reload'.$this->getChartId(), 'Neuladen', '');

        return [$button];
    }

    protected function getChartId(): string
    {
        return 'salesVolume';
    }

    protected function getStatsSystemName(): string
    {
        return self::ORDER_NUMBER_STATISTICS_GROUP_SYSTEM_NAME;
    }

    protected function generateBodyHtml(): string
    {
        $this->renderer->AddSourceObject('group', $this->getStatsGroup($this->getStatsSystemName()));
        $this->renderer->AddSourceObject('chartId', $this->getChartId());

        $renderedStatistic = $this->renderer->Render('@ChameleonSystemEcommerceStats/snippets-cms/ecommerceStats/module/barchart-body.html.twig');

        $this->renderer->AddSourceObject('reloadUrl', '/cms/api/dashboard/widget/chameleon_system_ecommerce_stats.bridge_chameleon_dashboard_widgets.sales_volume_dashboard_widget/getStatsDataAsJson');
        $renderedReloadJS = $this->renderer->Render('@ChameleonSystemEcommerceStats/snippets-cms/ecommerceStats/module/reloadingJs.html.twig');

        return "<div>
                    <div class='bg-white'>
                        ".$renderedStatistic.'
                    </div>
                </div>
               '.$renderedReloadJS;
    }

    public function getColorCssClass(): string
    {
        return 'text-white bg-info';
    }
}
