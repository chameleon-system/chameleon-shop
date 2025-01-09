<?php

namespace ChameleonSystem\EcommerceStatsBundle\Bridge\Chameleon\Dashboard\Widgets;

use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsCurrencyServiceInterface;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsTableServiceInterface;
use esono\pkgCmsCache\CacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AverageCartValueDashboardWidget extends DashboardBaseWidget
{
    private const CART_VALUE_STATISTICS_GROUP_SYSTEM_NAME = 'basket_size_without_shipping';

    public function __construct(
        CacheInterface $cache,
        \ViewRenderer $viewRenderer,
        StatsTableServiceInterface $statsTable,
        TranslatorInterface $translator,
        StatsCurrencyServiceInterface $currencyService,
        String $defaultTimeframe
    ) {
        parent::__construct($cache, $viewRenderer, $statsTable, $translator, $currencyService, $defaultTimeframe);
    }

    public function getTitle(): string
    {
        return $this->getStatsGroup($this->getStatsSystemName())?->getGroupTitle();
    }

    public function getDropdownItems(): array
    {
        return [];
    }

    protected function getStatsSystemName(): string
    {
        return self::CART_VALUE_STATISTICS_GROUP_SYSTEM_NAME;
    }

    protected function generateBodyHtml(): string
    {
        $this->renderer->AddSourceObject('group', $this->getStatsGroup($this->getStatsSystemName()));
        $this->renderer->AddSourceObject('chartId', 'averageCartValue');

        $renderedStatistic = $this->renderer->Render('@ChameleonSystemEcommerceStats/snippets-cms/ecommerceStats/module/barchart-body.html.twig');

        return "<div>
                    <div class='bg-white'>
                        ".$renderedStatistic.'
                    </div>
                </div>';
    }

    public function getColorCssClass(): string
    {
        return 'text-white bg-info';
    }
}
