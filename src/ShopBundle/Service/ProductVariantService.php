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
        // TODO does this need caching? (modules (and mappers?) might be cached?)

        // shop_article -> shop_variant_set -> shop_variant_type -> shop_variant_type_value

        if (true === $shopArticle->IsVariant()) {
            $shopArticle = $shopArticle->GetFieldVariantParent();
        }

        $variantSet = $shopArticle->GetFieldShopVariantSet();

        if (null === $variantSet) {
            return $shopArticle;
        }

        foreach ($typeSelection as $typeId => $valueId) {
            if (null === $valueId || '' === $valueId) {
                unset($typeSelection[$typeId]);
            }
        }

        $variantTypes = $variantSet->GetFieldShopVariantTypeList();
        $variantTypes->GoToStart();

        $properlySelected = [];

        // Adds OR removes entries to/from $typeSelection
        while (false !== ($variantType = $variantTypes->Next())) {
            // query article repeatedly for variant values and fill $properlySelected, if there is only one value: pick it

            // TODO / NOTE in order for this to work the selection logic in the frontend must first select type first in this list
            //   and not allow later ones to be selected
            //   This is for example implemented in \TPkgShopMapper_ArticleGetOneVariantType::Accept() ($aTmpSelectValue / bAllowSelection).

            $availableValues = $shopArticle->GetVariantValuesAvailableForType($variantType, $properlySelected);

            // TODO what about 0 === $availableValues => early exit?

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

        $variantList = $shopArticle->GetFieldShopArticleVariantsList($typeSelection);

        if (1 === $variantList->Length()) {
            return $variantList->Current();
        }

        return $shopArticle;
    }
}
