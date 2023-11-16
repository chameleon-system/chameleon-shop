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

class VariantTypeDataModelFactory implements VariantTypeDataModelFactoryInterface
{
    private string $dataModelClass = 'ChameleonSystem\ShopBundle\Library\DataModels\VariantTypeDataModel';
 
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

    /**
     * @inheritDoc
     */
    public function setDataModelClass(string $dataModelClass): void
    {
        if (!is_a($dataModelClass, VariantTypeDataModelInterface::class, true)) {
            throw new \InvalidArgumentException('dataModelClass must implement ' . VariantTypeDataModelInterface::class);
        }
        
        $this->dataModelClass = $dataModelClass;
    }
}
