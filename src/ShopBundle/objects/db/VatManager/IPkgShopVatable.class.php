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
 * objects to which a vat applies must implement this interface.
 */
interface IPkgShopVatable
{
    /**
     * @return TdbShopVat
     */
    public function GetVat();
}
