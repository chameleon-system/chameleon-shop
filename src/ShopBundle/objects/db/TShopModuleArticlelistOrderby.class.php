<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\ActivePageServiceInterface;

class TShopModuleArticlelistOrderby extends TShopModuleArticlelistOrderbyAutoParent
{
    /**
     * return order by string.
     *
     * @param string $sDirection - ASC or DESC. if set to NULL the value set in the class will be used
     *
     * @return string
     */
    public function GetOrderByString($sDirection = null)
    {
        if (is_null($sDirection)) {
            $sDirection = $this->fieldOrderDirection;
        }
        $sOrderBy = '';
        if (!empty($this->fieldSqlOrderBy)) {
            $sOrderBy = $this->fieldSqlOrderBy.' '.$sDirection;
        }

        if (!empty($this->fieldSqlSecondaryOrderByString)) {
            $sOrderBy .= ', '.$this->fieldSqlSecondaryOrderByString;
        }

        return $sOrderBy;
    }

    /**
     * return the url that can be used to change the order of the current page to this order.
     *
     * @return string
     *
     * @deprecated since 6.2.0 - no longer used.
     */
    public function GetURLToChangeOrder()
    {
        $aParameters = array(MTShopArticleCatalogCore::URL_ORDER_BY => $this->id);

        return $this->getActivePageService()->getLinkToActivePageRelative($aParameters);
    }

    /**
     * @return ActivePageServiceInterface
     */
    private function getActivePageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.active_page_service');
    }
}
