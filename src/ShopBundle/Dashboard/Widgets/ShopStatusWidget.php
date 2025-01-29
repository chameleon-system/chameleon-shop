<?php

namespace ChameleonSystem\ShopBundle\Dashboard\Widgets;

use ChameleonSystem\CmsDashboardBundle\Bridge\Chameleon\Attribute\ExposeAsApi;
use ChameleonSystem\CmsDashboardBundle\Bridge\Chameleon\Dashboard\Widgets\DashboardWidget;
use ChameleonSystem\CmsDashboardBundle\Bridge\Chameleon\Service\DashboardCacheService;
use ChameleonSystem\CmsDashboardBundle\DataModel\WidgetDropdownItemDataModel;
use ChameleonSystem\SearchBundle\Bridge\ShopSearchStatusService;
use ChameleonSystem\SecurityBundle\Service\SecurityHelperAccess;
use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

class ShopStatusWidget extends DashboardWidget
{
    private const WIDGET_NAME = 'widget-shop-status';

    public function __construct(
        protected readonly DashboardCacheService $dashboardCacheService,
        protected readonly \ViewRenderer $renderer,
        protected readonly TranslatorInterface $translator,
        protected readonly SecurityHelperAccess $securityHelperAccess,
        protected readonly ShopSearchStatusService $shopSearchStatusService,
        protected readonly ShopServiceInterface $shopService,
        protected readonly Connection $databaseConnection)
    {
        parent::__construct($dashboardCacheService, $translator);
    }

    public function getTitle(): string
    {
        return $this->translator->trans('chameleon_system_shop.widget.shop_status_widget.title');
    }

    public function showWidget(): bool
    {
        return $this->securityHelperAccess->isGranted('CMS_RIGHT_ECOMMERCE_STATS_SHOW_MODULE');
    }

    public function useWidgetContainerTemplate(): bool
    {
        return false;
    }

    public function getWidgetId(): string
    {
        return self::WIDGET_NAME;
    }

    public function getDropdownItems(): array
    {
        $reloadItem = new WidgetDropdownItemDataModel(
            'reload-'.$this->getWidgetId(),
            $this->translator->trans('chameleon_system_shop.widget.reload_button_label'),
            ''
        );

        $reloadItem->addDataAttribute('data-service-alias', $this->getWidgetId());

        return [
            $reloadItem,
        ];
    }

    #[ExposeAsApi(description: 'Call this method dynamically via API:/cms/api/dashboard/widget/{widgetServiceId}/getWidgetHtmlAsJson')]
    public function getWidgetHtmlAsJson(): JsonResponse
    {
        $data = [
            'htmlTable' => $this->getBodyHtml(true),
            'dateTime' => date('d.m.Y H:i'),
        ];

        return new JsonResponse(json_encode($data));
    }

    protected function generateBodyHtml(): string
    {
        $shops = $this->shopService->getAllShops();

        $shopsData = [];
        foreach ($shops as $hopId => $shopName) {
            $shopData = [];
            $shopData['name'] = $shopName;
            $shopData['id'] = $hopId;
            $shopData['categoryCount'] = $this->shopService->getCategoryCountForShop($hopId);

            $shopsData[] = $shopData;
        }
        $this->renderer->AddSourceObject('shopsData', $shopsData);

        $searchIndexStatusDataModel = $this->shopSearchStatusService->getSearchStatus();
        $this->renderer->AddSourceObject('searchIndexStatusDataModel', $searchIndexStatusDataModel);
        $this->renderer->AddSourceObject('manufacturerCount', $this->getManufacturerCount());
        $this->renderer->AddSourceObject('productGroupCount', $this->getProductGroupCount());
        $this->renderer->AddSourceObject('variantSetsCount', $this->getVariantSetsCount());
        $this->renderer->AddSourceObject('reloadEventButtonId', 'reload-'.$this->getWidgetId());

        return $this->renderer->Render('Dashboard/Widgets/shop-status.html.twig');
    }

    private function getManufacturerCount(): int
    {
        $query = 'SELECT COUNT(*) FROM `shop_manufacturer`';

        return $this->databaseConnection->fetchOne($query);
    }

    private function getProductGroupCount(): int
    {
        $query = 'SELECT COUNT(*) FROM `shop_article_group`';

        return $this->databaseConnection->fetchOne($query);
    }

    private function getVariantSetsCount(): int
    {
        $query = 'SELECT COUNT(*) FROM `shop_variant_set`';

        return $this->databaseConnection->fetchOne($query);
    }
}
