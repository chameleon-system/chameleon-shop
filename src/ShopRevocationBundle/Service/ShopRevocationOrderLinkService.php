<?php

namespace ChameleonSystem\ShopRevocationBundle\Service;

class ShopRevocationOrderLinkService
{
    public function linkRevocationToOrder(\TShopOrder $order, string $revocationId): void
    {
        if ('' === $revocationId) {
            throw new \InvalidArgumentException('Revocation ID must not be empty.');
        }

        $order->AllowEditByAll(true);

        try {
            if (false === $order->SaveFieldsFast([
                'shop_order_revocation_id' => $revocationId,
            ])) {
                throw new \RuntimeException('Failed to link revocation to order.');
            }
        } finally {
            $order->AllowEditByAll(false);
        }

        $order->sqlData['shop_order_revocation_id'] = $revocationId;
    }
}
