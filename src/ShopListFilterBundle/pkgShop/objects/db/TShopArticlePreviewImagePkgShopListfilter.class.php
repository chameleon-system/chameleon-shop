<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopArticlePreviewImagePkgShopListfilter extends TShopArticlePreviewImagePkgShopListfilterAutoParent
{
    /**
     * add cache parameters (trigger clear for render).
     *
     * @param array $aCacheParameters
     *
     * @deprecated since 6.2.0 - no longer used.
     */
    protected function AddCacheParameters(&$aCacheParameters)
    {
        parent::AddCacheParameters($aCacheParameters);
        // add cache trigger for filters that may select variants
        if (false === TdbShop::GetActiveItem()) {
            $oActiveFilter = TdbPkgShopListfilter::GetActiveInstance();
            if ($oActiveFilter) {
                $aFilter = $oActiveFilter->GetCurrentFilterAsArray();
                $aCacheParameters['sActiveFilter'] = serialize($aFilter);
            }
        }
    }
}
