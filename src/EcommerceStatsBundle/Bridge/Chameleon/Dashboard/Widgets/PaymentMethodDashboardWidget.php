<?php

namespace ChameleonSystem\EcommerceStatsBundle\Bridge\Chameleon\Dashboard\Widgets;

use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsCurrencyServiceInterface;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsTableServiceInterface;
use esono\pkgCmsCache\CacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PaymentMethodDashboardWidget extends DashboardBaseWidget
{
    private const PAYMENT_METHOD_STATISTICS_GROUP_SYSTEM_NAME = 'used_payments';

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
        $statsGroup = $this->getStatsGroup(self::PAYMENT_METHOD_STATISTICS_GROUP_SYSTEM_NAME);

        return $statsGroup->getGroupTitle();
    }

    public function getDropdownItems(): array
    {
        return [];
    }

    protected function generateBodyHtml(): string
    {
        $this->renderer->AddSourceObject('group', $this->getStatsGroup(self::PAYMENT_METHOD_STATISTICS_GROUP_SYSTEM_NAME));
        $this->renderer->AddSourceObject('chartId', 'paymentMethods');

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
