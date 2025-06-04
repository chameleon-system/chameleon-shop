<?php

namespace ChameleonSystem\EcommerceStatsBundle\Bridge\Chameleon\Dashboard\Widgets;

use ChameleonSystem\CmsDashboardBundle\Bridge\Chameleon\Service\DashboardCacheService;
use ChameleonSystem\CmsDashboardBundle\Library\Interfaces\ColorGeneratorServiceInterface;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsCurrencyServiceInterface;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsTableServiceInterface;
use ChameleonSystem\SecurityBundle\Service\SecurityHelperAccess;
use Symfony\Contracts\Translation\TranslatorInterface;

class CustomerTypeDashboardWidget extends DashboardBaseWidget
{
    private const CUSTOMER_TYPE_STATISTICS_GROUP_SYSTEM_NAME = 'customer_types';
    const string WIDGET_ID = 'widget-customer-type';

    public function __construct(
        DashboardCacheService $dashboardCacheService,
        \ViewRenderer $viewRenderer,
        StatsTableServiceInterface $statsTable,
        TranslatorInterface $translator,
        StatsCurrencyServiceInterface $currencyService,
        string $defaultTimeframe,
        ColorGeneratorServiceInterface $colorGeneratorService,
        SecurityHelperAccess $securityHelperAccess,
        bool $enableDashboard
    ) {
        DashboardBaseWidget::__construct($dashboardCacheService, $viewRenderer, $statsTable, $translator, $currencyService, $defaultTimeframe, $colorGeneratorService, $securityHelperAccess, $enableDashboard);
    }

    public function getWidgetId(): string
    {
        return self::WIDGET_ID;
    }

    protected function getStatsGroupSystemName(): string
    {
        return self::CUSTOMER_TYPE_STATISTICS_GROUP_SYSTEM_NAME;
    }
}
