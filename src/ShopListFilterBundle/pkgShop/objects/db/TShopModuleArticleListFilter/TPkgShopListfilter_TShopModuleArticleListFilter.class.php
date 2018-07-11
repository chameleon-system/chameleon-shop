<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopListfilter_TShopModuleArticleListFilter extends TPkgShopListfilter_TShopModuleArticleListFilterAutoParent
{
    private $bCanBeFiltered = false;

    /**
     * return the query used to select the records.
     * Note: the query should include order by, and a limit on the number of records in total, but no paging data
     * Note2: you should NOT overwrite this method. overwrite GetListQueryBase, GetListQueryOrderBy, and GetListQueryLimit instead.
     *
     * @param TdbShopModuleArticleList $oListConfig
     *
     * @return string
     */
    public function GetListQuery(&$oListConfig)
    {
        $this->bCanBeFiltered = $oListConfig->fieldCanBeFiltered;

        return parent::GetListQuery($oListConfig);
    }
}
