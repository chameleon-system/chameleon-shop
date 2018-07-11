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
 * interface IPkgShopOrderStatusData.
 *
 * any item that abstracts a TdbObject needs to implement this
 * the idea is, to remove the business logic from the TdbObjects - and abstract the data needed for that away from Tdb as well. Tdb is then only used as
 * storage. This interface defines how the data is mapped between the two
 */
interface IPkgShopOrderStatusData
{
    /**
     * returns an assoc array with the data of the object mapped to to the tdb fields.
     *
     * @return array
     */
    public function getDataAsTdbArray();
}
