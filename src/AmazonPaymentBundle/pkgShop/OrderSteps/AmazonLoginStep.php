<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\pkgShop\OrderSteps;

class AmazonLoginStep extends \TdbShopOrderStep
{
    protected function AllowAccessToStep($bRedirectToPreviousPermittedStep = false)
    {
        return false;
    }

    protected function isApplicableForBasket(\TShopBasket $basket)
    {
        return null !== $basket->getAmazonOrderReferenceId() || true === $basket->hasAmazonPaymentError();
    }
}
