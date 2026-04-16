<?php

namespace ChameleonSystem\ShopRevocationBundle\Service;

interface RevocationAdditionalDaysProviderInterface
{
    public function getAdditionalDays(\TShopOrder $order): int;
}
