<?php

namespace ChameleonSystem\ShopBundle\Dashboard\Widgets;

use ChameleonSystem\CmsDashboardBundle\Bridge\Chameleon\Dashboard\Widgets\DashboardWidget;
use ChameleonSystem\CmsDashboardBundle\Bridge\Chameleon\Service\DashboardCacheService;
use Doctrine\DBAL\Connection;
use Symfony\Contracts\Translation\TranslatorInterface;

class UsedVoucherSeriesDashboardWidget extends DashboardWidget
{
    private const WIDGET_NAME = 'widget-used-voucher-series';

    public function __construct(
        protected readonly DashboardCacheService $dashboardCacheService,
        protected readonly TranslatorInterface $translator,
        protected readonly \ViewRenderer $renderer,
        protected readonly Connection $databaseConnection
    )
    {
        parent::__construct($dashboardCacheService, $translator);
    }

    public function getTitle(): string
    {
        return $this->translator->trans('chameleon_system_shop.widget.used_voucher_series.title');
    }

    public function getWidgetId(): string
    {
        return self::WIDGET_NAME;
    }

    public function getDropdownItems(): array
    {
        return [];
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
                          AND `date_used_up` >= DATE_SUB(NOW(), INTERVAL 14 DAY)";

            $voucherCount = $this->databaseConnection->fetchOne($query, ['voucherSeriesId' => $voucherSeries['id']]);

            $chartData[] = ['seriesName' => $voucherSeries['name'], 'usedVoucherCount' => $voucherCount];
        }

        $this->renderer->AddSourceObject('chartData', $chartData);

        return $this->renderer->Render('Dashboard/Widgets/used-voucher-series.html.twig');
    }
}