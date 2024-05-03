<?php

namespace ChameleonSystem\ShopArticlePreorderBundle\EventListener;

use ChameleonSystem\ShopBundle\Event\UpdateProductStockEvent;

class UpdateProductStockListener
{
    public function sendPreOrderMail(UpdateProductStockEvent $event): void
    {
        if ($event->getOldStock() < 1 && $event->getNewStock() > 0) {
            $oShopArticlePreorderList = \TdbPkgShopArticlePreorderList::GetListForShopArticleId($event->getProductId());
            while ($oShopArticlePreorder = $oShopArticlePreorderList->Next()) {
                $oShopArticlePreorder->SendMail();
            }
        }
    }
}