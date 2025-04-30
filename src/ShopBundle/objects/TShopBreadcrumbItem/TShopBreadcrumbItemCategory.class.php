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
 * item is used to simulate a breadcrumb node.
 *
 * @extends TShopBreadcrumbItem<TdbShopCategory>
 */
class TShopBreadcrumbItemCategory extends TShopBreadcrumbItem
{
    /**
     * @param bool $bForcePortal
     *
     * @return string
     */
    public function GetLink($bForcePortal = false)
    {
        return $this->oItem->GetLink($bForcePortal);
    }

    /**
     * @return string
     */
    public function GetName()
    {
        return $this->oItem->GetBreadcrumbName();
    }
}
