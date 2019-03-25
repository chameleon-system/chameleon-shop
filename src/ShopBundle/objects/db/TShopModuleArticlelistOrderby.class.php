<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
}
