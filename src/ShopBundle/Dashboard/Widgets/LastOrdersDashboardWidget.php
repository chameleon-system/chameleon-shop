<?php

namespace ChameleonSystem\ShopBundle\Dashboard\Widgets;

use ChameleonSystem\CmsDashboardBundle\Bridge\Chameleon\Dashboard\Widgets\DashboardWidget;
use ChameleonSystem\CmsDashboardBundle\DataModel\WidgetDropdownItemDataModel;
use ChameleonSystem\ShopBundle\Dashboard\DataModel\LastOrdersItemDataModel;
use esono\pkgCmsCache\CacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LastOrdersDashboardWidget extends DashboardWidget
{
    public function __construct(
        protected readonly CacheInterface $cache,
        protected readonly \ViewRenderer $renderer,
        protected readonly TranslatorInterface $translator)
    {
        parent::__construct($cache);
    }

    public function getTitle(): string
    {
        return $this->translator->trans('chameleon_system_shop.widget.last_orders_title');
    }

    public function getDropdownItems(): array
    {
        return [new WidgetDropdownItemDataModel('LastOrdersDashboardWidget', 'Alle Bestellungen', '/cms?pagedef=tablemanager&id=268')];
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
            $lastOrderItemDataModel->setPaymentMethod($order->fieldShopPaymentMethodName);
            $lastOrderItemDataModel->setOrderItemCount($order->fieldCountArticles);
            $lastOrderItemDataModel->setDiscountValue($order->fieldValueDiscounts);
            $lastOrderItemDataModel->setOrderCanceled($order->fieldCanceled);
            $lastOrderItemDataModel->setPaymentSuccessful($order->fieldSystemOrderPaymentMethodExecuted);

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
}
