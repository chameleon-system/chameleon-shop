<?php

namespace ChameleonSystem\ShopBundle\Dashboard\Widgets;

use ChameleonSystem\CmsDashboardBundle\Bridge\Chameleon\Attribute\ExposeAsApi;
use ChameleonSystem\CmsDashboardBundle\Bridge\Chameleon\Dashboard\Widgets\DashboardWidget;
use ChameleonSystem\CmsDashboardBundle\Bridge\Chameleon\Service\DashboardCacheService;
use ChameleonSystem\CmsDashboardBundle\DataModel\WidgetDropdownItemDataModel;
use ChameleonSystem\EcommerceStatsBundle\Bridge\Chameleon\BackendModule\EcommerceStatsBackendModule;
use ChameleonSystem\SecurityBundle\Service\SecurityHelperAccess;
use ChameleonSystem\ShopBundle\Dashboard\DataModel\LastOrdersItemDataModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

class LastOrdersDashboardWidget extends DashboardWidget
{
    private const LAST_ORDER_SYSTEM_NAME = 'last-orders';
    public const CMS_RIGHT_SHOP_SHOW_ORDERS = 'CMS_RIGHT_ECOMMERCE_STATS_SHOW_MODULE';

    public function __construct(
        protected readonly DashboardCacheService $dashboardCacheService,
        protected readonly \ViewRenderer $renderer,
        protected readonly TranslatorInterface $translator,
        protected readonly SecurityHelperAccess $securityHelperAccess)
    {
        parent::__construct($dashboardCacheService, $translator);
    }

    public function getTitle(): string
    {
        return $this->translator->trans('chameleon_system_shop.widget.last_orders_title');
    }

    public function showWidget(): bool
    {
        return $this->securityHelperAccess->isGranted(self::CMS_RIGHT_SHOP_SHOW_ORDERS);
    }

    public function getDropdownItems(): array
    {
        $reloadItem = new WidgetDropdownItemDataModel('lastOrdersDashboardWidgetReload', $this->translator->trans('chameleon_system_shop.widget.reload_button_label'), '');
        $reloadItem->addDataAttribute('data-service-alias', 'widget-'.$this->getChartId());

        return [
            new WidgetDropdownItemDataModel('lastOrdersDashboardWidgetAllOrders', $this->translator->trans('chameleon_system_shop.widget.last_orders_all_orders'), '/cms?pagedef=tablemanager&id=268'),
            $reloadItem
        ];
    }

    public function getChartId(): string
    {
        return self::LAST_ORDER_SYSTEM_NAME;
    }

    protected function generateBodyHtml(): string
    {
        $orders = $this->getLastOrders();

        $this->renderer->AddSourceObject('orders', $orders);

        return $this->renderer->Render('Dashboard/Widgets/last-orders.html.twig');
    }

    #[ExposeAsApi(description: 'Call this method dynamically via API:/cms/api/dashboard/widget/{widgetServiceId}/getStatsDataAsJson')]
    public function getWidgetHtmlAsJson(): JsonResponse
    {
        $data = [
            'htmlTable' => $this->getBodyHtml(true),
            'dateTime' => date('d.m.Y H:i'),
        ];

        return new JsonResponse(json_encode($data));
    }

    private function getLastOrders(): array
    {
        $orderData = [];
        $query = 'SELECT * FROM `shop_order` ORDER BY `datecreated` DESC LIMIT 10';
        $orders = \TdbShopOrderList::GetList($query);
        while ($order = $orders->Next()) {
            $lastOrderItemDataModel = new LastOrdersItemDataModel();
            $lastOrderItemDataModel->setRecordId($order->id);
            $lastOrderItemDataModel->setOrderNumber($order->fieldOrdernumber);
            $lastOrderItemDataModel->setOrderDate($order->fieldDatecreated);
            $lastOrderItemDataModel->setOrderValue($order->fieldValueTotal);
            $lastOrderItemDataModel->setCustomerName($this->getCustomerName($order));
            $lastOrderItemDataModel->setCustomerEmail($order->fieldUserEmail);
            $lastOrderItemDataModel->setCustomerCity($order->fieldAdrBillingCity);
            $lastOrderItemDataModel->setCustomerCountryCode($order->GetFieldAdrBillingCountry()?->GetFieldTCountry()?->fieldIsoCode2 ?? '');
            $lastOrderItemDataModel->setOrderCurrencyCode($order->GetFieldPkgShopCurrency()?->fieldIso4217 ?? '');
            $lastOrderItemDataModel->setOrderCurrencySymbol($order->GetFieldPkgShopCurrency()?->fieldSymbol ?? '');
            $lastOrderItemDataModel->setPaymentMethod($order->fieldShopPaymentMethodName);
            $lastOrderItemDataModel->setOrderItemCount($order->fieldCountArticles);
            $lastOrderItemDataModel->setDiscountValue($order->fieldValueDiscounts);
            $lastOrderItemDataModel->setOrderCanceled($order->fieldCanceled);
            $lastOrderItemDataModel->setPaymentSuccessful($order->fieldSystemOrderPaymentMethodExecuted);
            $lastOrderItemDataModel->setDetailUrl($this->getDetailUrl($order));
            $lastOrderItemDataModel->setIsGuestOrder('' === $order->fieldDataExtranetUserId);

            $orderData[] = $lastOrderItemDataModel;
        }

        return $orderData;
    }

    private function getCustomerName(\TdbShopOrder $order)
    {
        $name = $order->fieldAdrBillingFirstname.' '.$order->fieldAdrBillingLastname;
        if (!empty($order->fieldAdrBillingCompany)) {
            $name .= ' ('.$order->fieldAdrBillingCompany.')';
        }

        return $name;
    }

    private function getDetailUrl(\TdbShopOrder $order): string
    {
        return '/cms?pagedef=tableeditor&tableid=268&id='.$order->id;
    }

    public function getFooterIncludes(): array
    {
        $includes = parent::getFooterIncludes();
        $includes[] = '<script type="text/javascript" src="/bundles/chameleonsystemshop/js/dashboard.js"></script>';

        return $includes;
    }

}
