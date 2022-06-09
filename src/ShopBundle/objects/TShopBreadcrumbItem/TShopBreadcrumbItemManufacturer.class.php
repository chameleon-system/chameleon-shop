<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @extends TShopBreadcrumbItem<TdbShopManufacturer>
 */
class TShopBreadcrumbItemManufacturer extends TShopBreadcrumbItem
{
    public function __construct(TdbShopManufacturer $manufacturer)
    {
        $this->oItem = $manufacturer;
    }

    /**
     * @param bool $bForcePortal
     * {@inheritDoc}
     */
    public function GetLink($bForcePortal = false)
    {
        return $this->oItem->GetLinkProducts();
    }
}
