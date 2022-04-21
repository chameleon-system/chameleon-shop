<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopListfilterItemCategoryTree extends TPkgShopListfilterItemMultiselectMLT
{
    /**
     * you need to set this to the table name of the connected table.
     *
     * @var string
     */
    protected $sItemTableName = 'shop_category';

    /**
     * you need to set this to the field name in the article table (note: the field is not derived from
     * the table name since this may differ).
     *
     * @var string
     */
    protected $sItemFieldName = 'shop_category_mlt';

    /**
     * returns std class as tree from the current active category to all available options.
     *
     * @return stdClass
     * @psalm-suppress UndefinedVariable, InvalidReturnType
     * @FIXME ???
     */
    public function GetTreeFromValueList()
    {
        $aValues = $oListItem->GetOptions();
    }
}
