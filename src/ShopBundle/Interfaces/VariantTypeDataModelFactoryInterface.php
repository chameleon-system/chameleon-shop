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

    public function setDataModelClass(string $dataModelClass): void;
}
