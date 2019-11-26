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
        $oldLimit = $variantList->GetActiveListLimit();
        $variantList->SetActiveListLimit(2);

        if (1 === $variantList->Length()) {
            $shopArticle = $variantList->Current();
        }

        $variantList->SetActiveListLimit($oldLimit);

        return $shopArticle;
    }
}
