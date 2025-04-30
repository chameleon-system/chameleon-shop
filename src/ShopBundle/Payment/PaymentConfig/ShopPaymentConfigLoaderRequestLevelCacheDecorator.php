<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\Payment\PaymentConfig;

use ChameleonSystem\ShopBundle\Payment\PaymentConfig\Interfaces\ShopPaymentConfigLoaderInterface;

class ShopPaymentConfigLoaderRequestLevelCacheDecorator implements ShopPaymentConfigLoaderInterface
{
    /**
     * @var ShopPaymentConfigLoaderInterface
     */
    private $subject;
    /**
     * @var array
     */
    private $cache = [];

    public function __construct(ShopPaymentConfigLoaderInterface $subject)
    {
        $this->subject = $subject;
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromOrderId($orderId)
    {
        // Do not cache, because we can't generate an efficient cache key here.
        return $this->subject->loadFromOrderId($orderId);
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromPaymentHandlerId($paymentHandlerId, $portalId)
    {
        $cacheKey = "loadFromPaymentHandlerId-$paymentHandlerId-$portalId";
        if (false === isset($this->cache[$cacheKey])) {
            $this->cache[$cacheKey] = $this->subject->loadFromPaymentHandlerId($paymentHandlerId, $portalId);
        }

        return $this->cache[$cacheKey];
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromPaymentHandlerGroupId($paymentGroupId, $portalId)
    {
        $cacheKey = "loadFromPaymentHandlerGroupId-$paymentGroupId-$portalId";
        if (false === isset($this->cache[$cacheKey])) {
            $this->cache[$cacheKey] = $this->subject->loadFromPaymentHandlerGroupId($paymentGroupId, $portalId);
        }

        return $this->cache[$cacheKey];
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromPaymentHandlerGroupSystemName($systemName, $portalId)
    {
        $cacheKey = "loadFromPaymentHandlerGroupSystemName-$systemName-$portalId";
        if (false === isset($this->cache[$cacheKey])) {
            $this->cache[$cacheKey] = $this->subject->loadFromPaymentHandlerGroupSystemName($systemName, $portalId);
        }

        return $this->cache[$cacheKey];
    }
}
