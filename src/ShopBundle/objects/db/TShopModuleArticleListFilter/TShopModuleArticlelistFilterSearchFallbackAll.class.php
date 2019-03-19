<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopModuleArticlelistFilterSearchFallbackAll extends TShopModuleArticlelistFilterSearch
{
    /**
     * prevent the use of the parent object when this filter finds not articles.
     *
     * @return bool
     */
    public function PreventUseOfParentObjectWhenNoRecordsAreFound()
    {
        return $this->getHasSearch();
    }

    /**
     * optional allows you to specify a list filter, that will be used instead, if this list filter has 0 matches.
     * if set, then this will overwrite, returns the parent as default
     * note: this will only be called if PreventUseOfParentObjectWhenNoRecordsAreFound returns false.
     *
     * @return TdbShopModuleArticleListFilter|null
     */
    public function getFallbackListFilter()
    {
        $aFilterData = $this->sqlData;
        /** @var TdbShopModuleArticleListFilter $oFilterObject */
        $oFilterObject = new TShopModuleArticlelistFilterAllArticles();
        $oFilterObject->LoadFromRow($aFilterData);

        return $oFilterObject;
    }
}
