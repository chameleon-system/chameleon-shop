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
/**/
class TShopBreadcrumbItemArticle extends TShopBreadcrumbItem
{
    public function GetLink()
    {
        $oShop = TdbShop::GetInstance();
        $oActiveCategory = $oShop->GetActiveCategory();
        $iCategoryId = null;
        if (!is_null($oActiveCategory)) {
            $iCategoryId = $oActiveCategory->id;
        }

        return $this->oItem->GetDetailLink(false, $iCategoryId);
    }

    public function GetName()
    {
        return $this->oItem->GetBreadcrumbName();
    }
}
