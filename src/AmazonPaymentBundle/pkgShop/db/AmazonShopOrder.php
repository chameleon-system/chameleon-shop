<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\pkgShop\db;

use ChameleonSystem\AmazonPaymentBundle\pkgShop\AmazonPaymentHandler;
use TdbDataExtranetUser;

class AmazonShopOrder extends \ChameleonSystemAmazonPaymentBundlepkgShopdbAmazonShopOrderAutoParent
{
    /**
     * returns amazon order reference id if the order was paid with amazon. throws an InvalidArgumentException if the
     * order was not paid with amazon.
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public function getAmazonOrderReferenceId()
    {
        $paymentHandler = $this->GetPaymentHandler();
        if (false === ($paymentHandler instanceof AmazonPaymentHandler)) {
            throw new \InvalidArgumentException('order was not paid with amazon payment');
        }

        /** @var $paymentHandler AmazonPaymentHandler */

        return $paymentHandler->getAmazonOrderReferenceId();
    }

    /**
     * {@inheritdoc}
     */
    public function CreateOrderInDatabaseCompleteHook()
    {
        parent::CreateOrderInDatabaseCompleteHook();
        TdbDataExtranetUser::GetInstance()->setAmazonPaymentEnabled(false);
    }
}
