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
    public const CMS_RIGHT_SHOP_SHOW_ORDERS = 'CMS_RIGHT_ECOMMERCE_STATS_SHOW_MODULE';
    public const string WIDGET_ID = 'widget-last-orders';

    public function __construct(
        protected readonly DashboardCacheService $dashboardCacheService,
        protected readonly \ViewRenderer $renderer,
        protected readonly TranslatorInterface $translator,
        protected readonly SecurityHelperAccess $securityHelperAccess,
        protected readonly bool $enableDashboard
    ) {
        parent::__construct($dashboardCacheService, $translator);
    }

    public function getTitle(): string
    {
        return $this->translator->trans('chameleon_system_shop.widget.last_orders.title');
    }

    public function showWidget(): bool
    {
        return $this->securityHelperAccess->isGranted(EcommerceStatsBackendModule::CMS_RIGHT_ECOMMERCE_STATS_SHOW_MODULE) && true === $this->enableDashboard;
    }

    public function getDropdownItems(): array
    {
        return [
            new WidgetDropdownItemDataModel('lastOrdersDashboardWidgetAllOrders', $this->translator->trans('chameleon_system_shop.widget.last_orders_all_orders'), '/cms?pagedef=tablemanager&id=268'),
        ];
    }

    public function getWidgetId(): string
    {
        return self::WIDGET_ID;
    }

    protected function generateBodyHtml(): string
    {
        $orders = $this->getLastOrders();

        $this->renderer->AddSourceObject('orders', $orders);

        return $this->renderer->Render('Dashboard/Widgets/last-orders.html.twig');
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

    private function getCustomerName(\TdbShopOrder $order): string
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
}
