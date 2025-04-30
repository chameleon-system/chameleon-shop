<?php

namespace ChameleonSystem\ShopBundle\Service;

use ChameleonSystem\ShopBundle\Interfaces\ProductVariantServiceInterface;

class ProductVariantService implements ProductVariantServiceInterface
{
    /**
     * {@inheritDoc}
     */
    public function getProductBasedOnSelection(\TdbShopArticle $shopArticle, array $typeSelection): \TdbShopArticle
    {
        if (true === $shopArticle->IsVariant() && 0 === \count($typeSelection)) {
            return $shopArticle;
        }

        if (true === $shopArticle->IsVariant()) {
            $shopArticle = $shopArticle->GetFieldVariantParent();
        }

        $variantList = $shopArticle->GetFieldShopArticleVariantsList($typeSelection);

        if (1 === $variantList->Length()) {
            /*
             * @psalm-suppress FalsableReturnStatement - `Current` return value cannot be false, we check the length above
             */
            return $variantList->Current();
        }

        return $shopArticle;
    }
}
