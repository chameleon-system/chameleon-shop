<?php

namespace ChameleonSystem\EcommerceStatsBundle\Bridge\Chameleon\Dashboard\Widgets;

use ChameleonSystem\CmsDashboardBundle\Bridge\Chameleon\Service\DashboardCacheService;
use ChameleonSystem\CmsDashboardBundle\Library\Interfaces\ColorGeneratorServiceInterface;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsCurrencyServiceInterface;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsTableServiceInterface;
use ChameleonSystem\SecurityBundle\Service\SecurityHelperAccess;
use Symfony\Contracts\Translation\TranslatorInterface;

class TopCategoriesDashboardWidget extends DashboardBaseWidget
{
    private const TOP_CATEGORIES_STATISTICS_GROUP_SYSTEM_NAME = 'top_categories';

    public function __construct(
        DashboardCacheService $dashboardCacheService,
        \ViewRenderer $viewRenderer,
        StatsTableServiceInterface $statsTable,
        TranslatorInterface $translator,
        StatsCurrencyServiceInterface $currencyService,
        string $defaultTimeframe,
        ColorGeneratorServiceInterface $colorGeneratorService,
        SecurityHelperAccess $securityHelperAccess
    ) {
        parent::__construct($dashboardCacheService, $viewRenderer, $statsTable, $translator, $currencyService, $defaultTimeframe, $colorGeneratorService, $securityHelperAccess);
    }

    public function getChartId(): string
    {
        return 'top-categories';
    }

    public function getColorCssClass(): string
    {
        return 'text-white bg-info';
    }

    protected function getStatsGroupSystemName(): string
    {
        return self::TOP_CATEGORIES_STATISTICS_GROUP_SYSTEM_NAME;
    }
}
