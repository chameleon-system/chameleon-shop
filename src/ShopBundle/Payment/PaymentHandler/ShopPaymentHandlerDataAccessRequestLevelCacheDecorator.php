<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\Payment\PaymentHandler;

use ChameleonSystem\ShopBundle\Payment\PaymentHandler\Interfaces\ShopPaymentHandlerDataAccessInterface;
use TdbShopPaymentHandler;

class ShopPaymentHandlerDataAccessRequestLevelCacheDecorator implements ShopPaymentHandlerDataAccessInterface
{
    /**
     * @var ShopPaymentHandlerDataAccessInterface
     */
    private $subject;
    /**
     * @var array
     */
    private $cache;

    /**
     * @param ShopPaymentHandlerDataAccessInterface $subject
     */
    public function __construct(ShopPaymentHandlerDataAccessInterface $subject)
    {
        $this->subject = $subject;
    }

    /**
     * {@inheritdoc}
     * This cache decorator always returns a new instance of the payment handler and caches only the contained data.
     * This is because the handler can potentially have own properties that must not be shared (might even be customer-
     * specific).
     */
    public function getBarePaymentHandler($paymentHandlerId, $languageId = null)
    {
        $cacheKey = $paymentHandlerId;
        if (false === isset($this->cache[$cacheKey])) {
            $paymentHandler = $this->subject->getBarePaymentHandler($paymentHandlerId, $languageId);
            if (null === $paymentHandler) {
                return null;
            }
            $this->cache[$cacheKey] = $paymentHandler->sqlData;

            return $paymentHandler;
        }

        return TdbShopPaymentHandler::getInstanceFromDataRow($this->cache[$cacheKey], $languageId);
    }
}
