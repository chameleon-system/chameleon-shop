<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopModuleArticleList extends TAdbShopModuleArticleList
{
    /**
     * set a new order by id - we need to do this using a method so that we can
     * reset the internal cache for the connected lookup.
     *
     * @param string $iId
     */
    public function UpdateOrderById($iId)
    {
        $this->sqlData['shop_module_articlelist_orderby_id'] = $iId;
        $this->fieldShopModuleArticlelistOrderbyId = $iId;
        $oItem = null;
        $this->SetInternalCache('oLookupshop_module_articlelist_orderby_id', $oItem);
    }
}
