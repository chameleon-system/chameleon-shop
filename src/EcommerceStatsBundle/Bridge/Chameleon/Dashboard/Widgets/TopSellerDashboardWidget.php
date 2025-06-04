<?php

namespace ChameleonSystem\EcommerceStatsBundle\Bridge\Chameleon\Dashboard\Widgets;

use ChameleonSystem\CmsDashboardBundle\Bridge\Chameleon\Service\DashboardCacheService;
use ChameleonSystem\CmsDashboardBundle\Library\Interfaces\ColorGeneratorServiceInterface;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsCurrencyServiceInterface;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsTableServiceInterface;
use ChameleonSystem\SecurityBundle\Service\SecurityHelperAccess;
use Symfony\Contracts\Translation\TranslatorInterface;

class TopSellerDashboardWidget extends DashboardBaseWidget
{
    private const TOP_SELLER_STATISTICS_GROUP_SYSTEM_NAME = 'top_seller';
    const string WIDGET_ID = 'top-seller';

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
        parent::__construct($dashboardCacheService, $viewRenderer, $statsTable, $translator, $currencyService, $defaultTimeframe, $colorGeneratorService, $securityHelperAccess, $enableDashboard);
    }

    public function getWidgetId(): string
    {
        return self::WIDGET_ID;
    }

    protected function getStatsGroupSystemName(): string
    {
        return self::TOP_SELLER_STATISTICS_GROUP_SYSTEM_NAME;
    }
}
