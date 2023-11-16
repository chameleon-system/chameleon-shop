<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\Interfaces;

use ChameleonSystem\ShopBundle\Library\DataModels\VariantTypeDataModelInterface;

interface VariantTypeDataModelFactoryInterface
{
    public function createFromVariantTypeRecord(
        \TdbShopVariantType $shopVariantType,
        bool $isSelectionAllowed
    ): VariantTypeDataModelInterface;

    /**
     * Set the data model class for the function.
     *
     * @param string $dataModelClass The class name of the data model including the namespace.
     * @throws \InvalidArgumentException If the dataModelClass does not implement VariantTypeDataModelInterface.
     * 
     * @return void
     */
    public function setDataModelClass(string $dataModelClass): void;
}
