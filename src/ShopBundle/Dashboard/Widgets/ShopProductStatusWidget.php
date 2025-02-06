<?php

namespace ChameleonSystem\ShopBundle\Dashboard\Widgets;

use ChameleonSystem\CmsDashboardBundle\Bridge\Chameleon\Dashboard\Widgets\DashboardWidget;
use ChameleonSystem\CmsDashboardBundle\Bridge\Chameleon\Service\DashboardCacheService;
use ChameleonSystem\SecurityBundle\Service\SecurityHelperAccess;
use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;
use Doctrine\DBAL\Connection;
use Symfony\Contracts\Translation\TranslatorInterface;

class ShopProductStatusWidget extends DashboardWidget
{
    private const WIDGET_NAME = 'widget-shop-product-status';

    public function __construct(
        protected readonly DashboardCacheService $dashboardCacheService,
        protected readonly \ViewRenderer $renderer,
        protected readonly TranslatorInterface $translator,
        protected readonly SecurityHelperAccess $securityHelperAccess,
        protected readonly ShopServiceInterface $shopService,
        protected readonly Connection $databaseConnection)
    {
        parent::__construct($dashboardCacheService, $translator);
    }

    public function getTitle(): string
    {
        return $this->translator->trans('chameleon_system_shop.widget.shop_product_status_widget.title');
    }

    public function getDropdownItems(): array
    {
        return [];
    }

    public function showWidget(): bool
    {
        return $this->securityHelperAccess->isGranted('CMS_RIGHT_ECOMMERCE_STATS_SHOW_MODULE');
    }

    public function getWidgetId(): string
    {
        return self::WIDGET_NAME;
    }

    protected function generateBodyHtml(): string
    {
        $chartData = $this->buildChartData();
        $this->renderer->AddSourceObject('chartData', $chartData);
        $this->renderer->AddSourceObject('reloadEventButtonId', 'reload-'.$this->getWidgetId());

        return $this->renderer->Render('Dashboard/Widgets/shop-product-status.html.twig');
    }

    private function buildChartData(): array
    {
        $chartData = [];
        $shopList = $this->shopService->getAllShops();

        foreach ($shopList as $shopId => $shopName) {
            $shopData = [
                'name' => $shopName,
                'total' => 0,
                'totalActive' => 0,
                'totalSearchable' => 0,
                'totalNew' => 0,
                'types' => [],
            ];

            $types = [
                'main' => $this->shopService->getProductCountForShop($shopId, onlyMainProducts: true),
                'variant' => $this->shopService->getProductCountForShop($shopId, onlyVariants: true),
                'virtual' => $this->shopService->getProductCountForShop($shopId, isVirtualProduct: true),
            ];

            foreach ($types as $typeName => $typeCount) {
                $shopData['types'][$typeName] = [
                    'total' => $typeCount,
                    'active' => $this->shopService->getProductCountForShop(
                        shopId: $shopId,
                        onlyActive: true,
                        onlyMainProducts: 'main' === $typeName,
                        onlyVariants: 'variant' === $typeName,
                        isVirtualProduct: 'virtual' === $typeName
                    ),
                    'searchable' => $this->shopService->getProductCountForShop(
                        shopId: $shopId,
                        onlyMainProducts: 'main' === $typeName,
                        onlyVariants: 'variant' === $typeName,
                        isVirtualProduct: 'virtual' === $typeName,
                        isSearchable: true
                    ),
                    'new' => $this->shopService->getProductCountForShop(
                        shopId: $shopId,
                        onlyMainProducts: 'main' === $typeName,
                        onlyVariants: 'variant' === $typeName,
                        isVirtualProduct: 'virtual' === $typeName,
                        isNew: true
                    ),
                ];

                $shopData['total'] += $shopData['types'][$typeName]['total'];
                $shopData['totalActive'] += $shopData['types'][$typeName]['active'];
                $shopData['totalSearchable'] += $shopData['types'][$typeName]['searchable'];
                $shopData['totalNew'] += $shopData['types'][$typeName]['new'];
            }

            $chartData[] = $shopData;
        }

        return $chartData;
    }

    public function getFooterIncludes(): array
    {
        $includes = parent::getFooterIncludes();
        $includes[] = '<script type="text/javascript" src="/bundles/chameleonsystemcmsdashboard/js/chart.4.4.7.js"></script>';
        $includes[] = '<script type="text/javascript" src="/bundles/chameleonsystemcmsdashboard/js/chart-init.4.4.7.js"></script>';

        return $includes;
    }
}
