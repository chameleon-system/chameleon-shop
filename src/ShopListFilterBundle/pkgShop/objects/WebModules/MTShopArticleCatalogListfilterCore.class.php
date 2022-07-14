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
 * @deprecated since 6.2.0 - no longer used.
 */
class MTShopArticleCatalogListfilterCore extends MTShopArticleCatalogCore
{
    /**
     * load the article list and store it in $this->oList.
     *
     * @return void
     */
    protected function LoadArticleList()
    {
        if (is_null($this->oList)) {
            parent::LoadArticleList();
            if (!is_null($this->oList) && '1' == $this->global->GetUserData(TdbPkgShopListfilter::URL_PARAMETER_IS_NEW_REQUEST)) {
                TdbShop::ResetAllRegisteredActiveVariantsForAllSpots();
                $this->iPage = 0;
                $this->oList->RestorePagingInfoFromSession($this->iPageSize * $this->iPage, $this->iPageSize);
                $this->oList->JumpToPage(0);
            }
        }
    }
}
