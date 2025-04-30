<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\RuntimeCache;

use ChameleonSystem\ShopBundle\Interfaces\DataAccess\PaymentMethodDataAccessInterface;

class PaymentMethodDataAccessRuntimeCacheDecorator implements PaymentMethodDataAccessInterface
{
    /**
     * @var PaymentMethodDataAccessInterface
     */
    private $subject;

    /**
     * @var array
     */
    private $cache = [];

    public function __construct(PaymentMethodDataAccessInterface $subject)
    {
        $this->subject = $subject;
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingCountryIds($paymentMethodId)
    {
        $key = sprintf('shippingCountryIds-%s', $paymentMethodId);
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }
        $this->cache[$key] = $this->subject->getShippingCountryIds($paymentMethodId);

        return $this->cache[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function getBillingCountryIds($paymentMethodId)
    {
        $key = sprintf('billingCountryIds-%s', $paymentMethodId);
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }
        $this->cache[$key] = $this->subject->getBillingCountryIds($paymentMethodId);

        return $this->cache[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function getPermittedUserIds($paymentMethodId)
    {
        $key = sprintf('permittedUserIds-%s', $paymentMethodId);
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }
        $this->cache[$key] = $this->subject->getPermittedUserIds($paymentMethodId);

        return $this->cache[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function getPermittedUserGroupIds($paymentMethodId)
    {
        $key = sprintf('permittedUserGroupIds-%s', $paymentMethodId);
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }
        $this->cache[$key] = $this->subject->getPermittedUserGroupIds($paymentMethodId);

        return $this->cache[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function getPermittedArticleIds($paymentMethodId)
    {
        $key = sprintf('articleIdRestrictions-%s', $paymentMethodId);
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }
        $this->cache[$key] = $this->subject->getPermittedArticleIds($paymentMethodId);

        return $this->cache[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function getPermittedCategoryIds($paymentMethodId)
    {
        $key = sprintf('categoryIdRestrictions-%s', $paymentMethodId);
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }
        $this->cache[$key] = $this->subject->getPermittedCategoryIds($paymentMethodId);

        return $this->cache[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function getPermittedArticleGroupIds($paymentMethodId)
    {
        $key = sprintf('articleGroupIdRestrictions-%s', $paymentMethodId);
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }
        $this->cache[$key] = $this->subject->getPermittedArticleGroupIds($paymentMethodId);

        return $this->cache[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function getInvalidArticleIds($paymentMethodId)
    {
        $key = sprintf('invalidArticleIds-%s', $paymentMethodId);
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }
        $this->cache[$key] = $this->subject->getInvalidArticleIds($paymentMethodId);

        return $this->cache[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function getInvalidCategoryIds($paymentMethodId)
    {
        $key = sprintf('invalidCategoryIds-%s', $paymentMethodId);
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }
        $this->cache[$key] = $this->subject->getInvalidCategoryIds($paymentMethodId);

        return $this->cache[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function getInvalidArticleGroupIds($paymentMethodId)
    {
        $key = sprintf('invalidArticleGroupIds-%s', $paymentMethodId);
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }
        $this->cache[$key] = $this->subject->getInvalidArticleGroupIds($paymentMethodId);

        return $this->cache[$key];
    }
}
