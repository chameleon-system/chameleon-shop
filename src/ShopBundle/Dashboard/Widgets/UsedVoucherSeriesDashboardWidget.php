<?php

namespace ChameleonSystem\ShopBundle\Dashboard\Widgets;

use ChameleonSystem\CmsDashboardBundle\Bridge\Chameleon\Dashboard\Widgets\DashboardWidget;
use ChameleonSystem\CmsDashboardBundle\Bridge\Chameleon\Service\DashboardCacheService;
use ChameleonSystem\EcommerceStatsBundle\Bridge\Chameleon\BackendModule\EcommerceStatsBackendModule;
use ChameleonSystem\SecurityBundle\Service\SecurityHelperAccess;
use Doctrine\DBAL\Connection;
use Symfony\Contracts\Translation\TranslatorInterface;

class UsedVoucherSeriesDashboardWidget extends DashboardWidget
{
    public const string WIDGET_ID = 'widget-used-voucher-series';

    public const int USED_VOUCHER_DAYS_INTERVAL = 14;

    public function __construct(
        protected readonly DashboardCacheService $dashboardCacheService,
        protected readonly TranslatorInterface $translator,
        protected readonly \ViewRenderer $renderer,
        protected readonly Connection $databaseConnection,
        protected readonly SecurityHelperAccess $securityHelperAccess,
        protected readonly bool $enableDashboard
    ) {
        parent::__construct($dashboardCacheService, $translator);
    }

    public function getTitle(): string
    {
        return $this->translator->trans('chameleon_system_shop.widget.used_voucher_series.title');
    }

    public function showWidget(): bool
    {
        return $this->securityHelperAccess->isGranted(EcommerceStatsBackendModule::CMS_RIGHT_ECOMMERCE_STATS_SHOW_MODULE) && true === $this->enableDashboard;
    }

    public function getWidgetId(): string
    {
        return self::WIDGET_ID;
    }

    public function getDropdownItems(): array
    {
        return [];
    }

    public function getFooterIncludes(): array
    {
        $includes = parent::getFooterIncludes();
        $includes[] = '<script type="text/javascript" src="/bundles/chameleonsystemcmsdashboard/js/chart.4.4.7.js"></script>';
        $includes[] = '<script type="text/javascript" src="/bundles/chameleonsystemcmsdashboard/js/chartjs-adapter-date-fns.3.0.0.js"></script>';
        $includes[] = '<script type="text/javascript" src="/bundles/chameleonsystemcmsdashboard/js/chart-init.4.4.7.js"></script>';

        return $includes;
    }

    protected function generateBodyHtml(): string
    {
        $chartData = [];

        $query = "SELECT * FROM `shop_voucher_series`
                    WHERE `active` = '1'";

        $voucherSerieses = $this->databaseConnection->fetchAllAssociative($query);

        foreach ($voucherSerieses as $voucherSeries) {
            $query = "SELECT COUNT(*) as voucherCount FROM `shop_voucher`
                          WHERE `shop_voucher_series_id` = :voucherSeriesId
                          AND `is_used_up` = '1'
                          AND `date_used_up` >= DATE_SUB(NOW(), INTERVAL .". self::USED_VOUCHER_DAYS_INTERVAL . " DAY)";

            $voucherCount = $this->databaseConnection->fetchOne($query, ['voucherSeriesId' => $voucherSeries['id']]);

            if('0' === $voucherCount) {continue;}

            $chartData[] = ['seriesName' => $voucherSeries['name'], 'usedVoucherCount' => $voucherCount];
        }

        $this->renderer->AddSourceObject('chartData', $chartData);

        return $this->renderer->Render('Dashboard/Widgets/used-voucher-series.html.twig');
    }
}
