<?php

namespace ChameleonSystem\EcommerceStatsBundle\Bridge\Chameleon\Dashboard\Widgets;

use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsCurrencyServiceInterface;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsTableServiceInterface;
use esono\pkgCmsCache\CacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class OrderNumberDashboardWidget extends DashboardBaseWidget
{
    private const ORDER_NUMBER_STATISTICS_GROUP_SYSTEM_NAME = 'sales_count';

    public function __construct(
        CacheInterface $cache,
        \ViewRenderer $viewRenderer,
        StatsTableServiceInterface $statsTable,
        TranslatorInterface $translator,
        StatsCurrencyServiceInterface $currencyService
    ) {
        parent::__construct($cache, $viewRenderer, $statsTable, $translator, $currencyService);
    }

    public function getTitle(): string
    {
        return $this->translator->trans('chameleon_system_dashboard_widget.order_number_label');
    }

    public function getDropdownItems(): array
    {
        return [];
    }

    protected function generateBodyHtml(): string
    {
        $this->renderer->AddSourceObject('group', $this->getStatsGroup('sales_count'));
        $this->renderer->AddSourceObject('chartId', 'orderNumber');

        $renderedStatistic = $this->renderer->Render('@ChameleonSystemEcommerceStats/snippets-cms/ecommerceStats/module/barchart-body.html.twig');

        return "<div>
                    <div class='bg-white'>
                        ".$renderedStatistic.'
                    </div>
                </div>';
    }

    protected function getStatisticGroupSystemName(): string
    {
        return self::ORDER_NUMBER_STATISTICS_GROUP_SYSTEM_NAME;
    }

    public function getColorCssClass(): string
    {
        return 'text-white bg-info';
    }
}
