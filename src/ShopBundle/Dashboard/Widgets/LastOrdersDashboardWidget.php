<?php

namespace ChameleonSystem\ShopBundle\Dashboard\Widgets;

use ChameleonSystem\CmsDashboardBundle\Bridge\Chameleon\Dashboard\Widgets\DashboardWidget;
use ChameleonSystem\CmsDashboardBundle\Bridge\Chameleon\Service\DashboardCacheService;
use ChameleonSystem\CmsDashboardBundle\DataModel\WidgetDropdownItemDataModel;
use ChameleonSystem\ShopBundle\Dashboard\DataModel\LastOrdersItemDataModel;
use Symfony\Contracts\Translation\TranslatorInterface;

class LastOrdersDashboardWidget extends DashboardWidget
{
    private const LAST_ORDER_SYSTEM_NAME = 'lastOrders';

    public function __construct(
        protected readonly DashboardCacheService $dashboardCacheService,
        protected readonly \ViewRenderer $renderer,
        protected readonly TranslatorInterface $translator)
    {
        parent::__construct($dashboardCacheService);
    }

    public function getTitle(): string
    {
        return $this->translator->trans('chameleon_system_shop.widget.last_orders_title');
    }

    public function getDropdownItems(): array
    {
        return [new WidgetDropdownItemDataModel('LastOrdersDashboardWidget', 'Alle Bestellungen', '/cms?pagedef=tablemanager&id=268')];
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
}
