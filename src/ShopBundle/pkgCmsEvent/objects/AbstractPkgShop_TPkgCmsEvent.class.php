<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class AbstractPkgShop_TPkgCmsEvent extends TPkgCmsEvent
{
    const CONTEXT_PKG_SHOP = 'pkgShop';
    const NAME_USER_CHANGED_SHIPPING_COUNTRY = 'usrChangedShippingCountry';
    const NAME_USER_CHANGED_BILLING_COUNTRY = 'usrChangedBillingCountry';
}
