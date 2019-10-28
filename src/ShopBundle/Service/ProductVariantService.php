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
        // TODO compare  \TShopVariantDisplayHandler::GetArticleMatchingCurrentSelection() and \TShopArticle::GetVariantFromValues()?

        // TODO \TShop::GetActiveItemVariant() is similar but still assumes that an article must be "fully" selected (all variant types clicked)

        // shop_article -> shop_variant_set -> shop_variant_type -> shop_variant_type_value

        foreach ($typeSelection as $typeId => $valueId) {
            if (null === $valueId || '' === $valueId) {
                unset($typeSelection[$typeId]);
            }
        }

        if (true === $shopArticle->IsVariant()) {
            $shopArticle = $shopArticle->GetFieldVariantParent();
        }

        // TODO does this need to be set?
        $variantSet = $shopArticle->GetFieldShopVariantSet();

        if (null === $variantSet) {
            return $shopArticle;
        }

        $variantTypes = $variantSet->GetFieldShopVariantTypeList();
        $variantTypes->GoToStart();

        $properlySelected = [];

        // copied from \TShopVariantDisplayHandler::GetActiveVariantTypeSelection() (= second half)
        // Adds OR filters entries to/from $typeSelection
        while (false !== ($variantType = $variantTypes->Next())) {
            // query article repeatedly and fill $properlySelected, if there is only one option: auto pick it

            // TODO / NOTE in order for this to work the selection logic in the frontend must first select type first in this list
            //   and not allow later ones to be selected

            $availableValues = $shopArticle->GetVariantValuesAvailableForType($variantType, $properlySelected);

            // TODO what about null === $availableValues => early exit?

            $availableValues->GoToStart();

            if (true === \array_key_exists($variantType->id, $typeSelection)) {
                if (false === $availableValues->IsInList($typeSelection[$variantType->id])) {
                    unset($typeSelection[$variantType->id]);
                }
            }
            $availableValues->GoToStart();

            if (false === \array_key_exists($variantType->id, $typeSelection)) {
                if (1 === $availableValues->Length()) {
                    $typeSelection[$variantType->id] = $availableValues->Current()->id;
                }
            }

            if (true === \array_key_exists($variantType->id, $typeSelection)) {
                $properlySelected[$variantType->id] = $typeSelection[$variantType->id];
            }
        }

        // this part copied from \TShop::GetActiveItemVariant($shopArticle)

        $variantList = $shopArticle->GetFieldShopArticleVariantsList($typeSelection);

        if (1 === $variantList->Length()) {
            return $variantList->Current();
        }

        return $shopArticle;
    }
}
