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
        if (true === $shopArticle->IsVariant() && \count($typeSelection) === 0) {
            return $shopArticle;
        }

        if (true === $shopArticle->IsVariant()) {
            $shopArticle = $shopArticle->GetFieldVariantParent();
        }

        $variantList = $shopArticle->GetFieldShopArticleVariantsList($typeSelection);

        if (1 === $variantList->Length()) {
            return $variantList->Current();
        }

        return $shopArticle;
    }
}
