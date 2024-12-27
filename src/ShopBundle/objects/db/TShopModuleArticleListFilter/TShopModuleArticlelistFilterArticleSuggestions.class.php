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
 * this is just a stub that acts just like a manuell selection for now - we need to define
 * some algorithm that finds articles related to the current article (such as matching tags, attributes, etc).
 * /**/
class TShopModuleArticlelistFilterArticleSuggestions extends TdbShopModuleArticleListFilter
{
    /**
     * return any cache relevant parameters to the list class here.
     *
     * @return array
     */
    public function _GetCacheParameters()
    {
        $aParams = parent::_GetCacheParameters();
        $oActiveArticle = ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveProduct();
        if (!is_null($oActiveArticle)) {
            $aParams['articleId'] = $oActiveArticle->id;
        }

        return $aParams;
    }
}
