<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class AbstractPkgShopPaymentHandlerMapper extends AbstractViewMapper
{
    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements)
    {
        $oRequirements->NeedsSourceObject('oPaymentHandler', 'TdbShopPaymentHandler');
        $oRequirements->NeedsSourceObject('sPaymentMethodId', 'string'); // the payment method using the payment handler
    }
}
