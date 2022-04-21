<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopListfilterItemBoolean extends TdbPkgShopListfilterItem
{
    /**
     * return the current active value (0 or 1). if none is set, we will return 0.
     *
     * @return int
     * @psalm-suppress InvalidReturnType, InvalidReturnStatement
     * @FIXME We can't be sure that the return type is `int`. Better add a cast here.
     */
    public function GetActiveValue()
    {
        $iValue = $this->aActiveFilterData;
        if (is_null($iValue) || ('0' != $iValue && '1' != $iValue)) {
            $iValue = '';
        }

        return $iValue;
    }
}
