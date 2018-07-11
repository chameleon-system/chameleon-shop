<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopArticlePkgShopListfilterList extends TShopArticlePkgShopListfilterListAutoParent
{
    /**
     * list all parameters that you do not want to be included in the page links.
     *
     * @return array
     */
    public static function GetParametersToIgnoreInPageLinks()
    {
        $aParameters = parent::GetParametersToIgnoreInPageLinks();
        $aParameters[] = TdbPkgShopListfilter::URL_PARAMETER_IS_NEW_REQUEST;

        return $aParameters;
    }
}
