<?php

namespace ChameleonSystem\EcommerceStatsBundle\Bridge\Chameleon\Dashboard\Widgets;

use ChameleonSystem\CmsDashboardBundle\Bridge\Chameleon\Dashboard\Widgets\DashboardWidget;
use ChameleonSystem\EcommerceStatsBundle\Bridge\Chameleon\BackendModule\EcommerceStatsBackendModule;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsCurrencyServiceInterface;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsTableServiceInterface;
use DateTime;
use esono\pkgCmsCache\CacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use ViewRenderer;

class SalesVolumeDashboardWidget extends DashboardWidget
{
    private const SALES_VOLUME_STATISTICS_GROUP_ID = '74292e9c-2b9a-11df-9c53-00fcefbad5fb';

    private ViewRenderer $renderer;

    private StatsTableServiceInterface $stats;
    private TranslatorInterface $translator;
    private StatsCurrencyServiceInterface $statsCurrencyService;

    public function __construct(
        CacheInterface $cache,
        ViewRenderer $renderer,
        StatsTableServiceInterface $stats,
        TranslatorInterface $translator,
        StatsCurrencyServiceInterface $statsCurrencyService
    )
    {
        parent::__construct($cache);

        $this->renderer = $renderer;
        $this->stats = $stats;
        $this->translator = $translator;
        $this->statsCurrencyService = $statsCurrencyService;
    }

    public function getTitle(): string
    {
        return $this->translator->trans('chameleon_system_dashboard_widget.sales_volume_label');
    }

    public function getDropdownItems(): array
    {
        return [ ];
    }

    protected function generateBodyHtml(): string
    {
        $endDate = new DateTime('now');

        $startDate = clone $endDate;
        $startDate->modify(DashboardWidget::DEFAULT_TIMEFRAME_FOR_STATS);

        $statistic = $this->stats->evaluate(
            $startDate,
            $endDate,
            'day',
            false,
            '',
            $this->statsCurrencyService->getCurrencyIdByIsoCode(EcommerceStatsBackendModule::STANDARD_CURRENCY_ISO_CODE),
            self::SALES_VOLUME_STATISTICS_GROUP_ID
        );

        $this->renderer->AddSourceObject('group', $statistic->getBlocks()['Revenue excluding shipping']);
        $this->renderer->AddSourceObject('chartId', random_int(1, 999999));

        $renderedStatistic = $this->renderer->Render('@ChameleonSystemEcommerceStats/snippets-cms/ecommerceStats/module/barchart-body.html.twig');

        return "<div>
                    <div class='bg-white'>
                        ".$renderedStatistic."
                    </div>
                </div>";
    }

    public function getFooterIncludes(): array
    {
        $includes = parent::getFooterIncludes();
        $includes[] = '<script type="text/javascript" src="/bundles/chameleonsystemecommercestats/ecommerce_stats/js/chart.4.4.7.js"></script>';
        $includes[] = '<script type="text/javascript" src="/bundles/chameleonsystemecommercestats/ecommerce_stats/js/chart-init.4.4.7.js"></script>';

        return $includes;
    }

    public function getColorCssClass(): string
    {
        return 'text-white bg-info';
    }
}