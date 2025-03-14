<?php

namespace ChameleonSystem\EcommerceStatsBundle\Bridge\Chameleon\Dashboard\Widgets;

use ChameleonSystem\CmsDashboardBundle\Bridge\Chameleon\Service\DashboardCacheService;
use ChameleonSystem\CmsDashboardBundle\Library\Interfaces\ColorGeneratorServiceInterface;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsCurrencyServiceInterface;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsTableServiceInterface;
use ChameleonSystem\SecurityBundle\Service\SecurityHelperAccess;
use Symfony\Contracts\Translation\TranslatorInterface;

class PaymentMethodDashboardWidget extends DashboardBaseWidget
{
    private const PAYMENT_METHOD_STATISTICS_GROUP_SYSTEM_NAME = 'used_payments';

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
        return 'payment-method';
    }

    protected function getStatsGroupSystemName(): string
    {
        return self::PAYMENT_METHOD_STATISTICS_GROUP_SYSTEM_NAME;
    }
}
