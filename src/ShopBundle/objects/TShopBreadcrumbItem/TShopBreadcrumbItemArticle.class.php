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
 * @extends TShopBreadcrumbItem<TdbShopArticle>
 */
class TShopBreadcrumbItemArticle extends TShopBreadcrumbItem
{
    public function GetLink()
    {
        $oActiveCategory = ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveCategory();
        $iCategoryId = null;
        if (!is_null($oActiveCategory)) {
            $iCategoryId = $oActiveCategory->id;
        }

        return $this->oItem->getLink(false, null, [TdbShopArticle::CMS_LINKABLE_OBJECT_PARAM_CATEGORY => $iCategoryId]);
    }

    /**
     * @return string
     */
    public function GetName()
    {
        return $this->oItem->GetBreadcrumbName();
    }
}
