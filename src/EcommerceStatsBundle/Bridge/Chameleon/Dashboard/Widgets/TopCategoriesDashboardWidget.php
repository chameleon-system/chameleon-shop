<?php

namespace ChameleonSystem\EcommerceStatsBundle\Bridge\Chameleon\Dashboard\Widgets;

use ChameleonSystem\CmsDashboardBundle\DataModel\WidgetDropdownItemDataModel;
use ChameleonSystem\CmsDashboardBundle\Library\Interfaces\ColorGeneratorServiceInterface;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsCurrencyServiceInterface;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsTableServiceInterface;
use esono\pkgCmsCache\CacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TopCategoriesDashboardWidget extends DashboardBaseWidget
{
    private const TOP_CATEGORIES_STATISTICS_GROUP_SYSTEM_NAME = 'top_categories';

    public function __construct(
        CacheInterface $cache,
        \ViewRenderer $viewRenderer,
        StatsTableServiceInterface $statsTable,
        TranslatorInterface $translator,
        StatsCurrencyServiceInterface $currencyService,
        String $defaultTimeframe,
        ColorGeneratorServiceInterface $colorGeneratorService
    ) {
        DashboardBaseWidget::__construct($cache, $viewRenderer, $statsTable, $translator, $currencyService, $defaultTimeframe, $colorGeneratorService);
    }

    protected function getChartId(): string
    {
        return 'topcategories';
    }

    protected function getStatsSystemName(): string
    {
        return self::TOP_CATEGORIES_STATISTICS_GROUP_SYSTEM_NAME;
    }

    protected function generateBodyHtml(): string
    {
        $this->renderer->AddSourceObject('group', $this->getStatsGroup($this->getStatsSystemName()));
        $this->renderer->AddSourceObject('chartId', $this->getChartId());

        $renderedStatistic = $this->renderer->Render('@ChameleonSystemEcommerceStats/snippets-cms/ecommerceStats/module/barchart-body.html.twig');

        $this->renderer->AddSourceObject('reloadUrl', '/cms/api/dashboard/widget/chameleon_system_ecommerce_stats.bridge_chameleon_dashboard_widgets.top_categories_dashboard_widget/getStatsDataAsJson');
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
