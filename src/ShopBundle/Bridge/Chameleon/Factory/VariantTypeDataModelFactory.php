<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\Bridge\Chameleon\Factory;

use ChameleonSystem\ShopBundle\Interfaces\VariantTypeDataModelFactoryInterface;
use ChameleonSystem\ShopBundle\Library\DataModels\VariantTypeDataModelInterface;

/**
 * You may overwrite this class with your own and set a custom dataModelClass via config parameters.
 *
 * @example chameleon_system_shop.shop_variant_type.data_model: "Esono\\CustomerBundle\\DataModel\\VariantTypeDataModel"
 */
class VariantTypeDataModelFactory implements VariantTypeDataModelFactoryInterface
{
    private string $dataModelClass;

    public function __construct(string $dataModelClass)
    {
        $this->dataModelClass = $dataModelClass;
        $this->validateDataModelClass($dataModelClass);
    }

    public function createFromVariantTypeRecord(
        \TdbShopVariantType $shopVariantType,
        bool $isSelectionAllowed): VariantTypeDataModelInterface
    {
        return new $this->dataModelClass(
            $shopVariantType->fieldName,
            $shopVariantType->fieldIdentifier,
            $shopVariantType->fieldCmsMediaId,
            $isSelectionAllowed
        );
    }

    private function validateDataModelClass(string $dataModelClass): void
    {
        if (!is_a($dataModelClass, VariantTypeDataModelInterface::class, true)) {
            throw new \InvalidArgumentException('dataModelClass must implement '.VariantTypeDataModelInterface::class);
        }
    }
}
