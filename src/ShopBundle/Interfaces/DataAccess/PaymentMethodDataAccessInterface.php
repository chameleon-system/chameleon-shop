<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\Interfaces\DataAccess;

/**
 * access payment method configuration.
 */
interface PaymentMethodDataAccessInterface
{
    /**
     * Return a list of shipping countries for which the payment method is allowed. Empty array means no restriction.
     *
     * @param string $paymentMethodId
     *
     * @return array
     */
    public function getShippingCountryIds($paymentMethodId);

    /**
     * Return a list of billing countries for which the payment method is allowed. Empty array means no restriction.
     *
     * @param string $paymentMethodId
     *
     * @return array
     */
    public function getBillingCountryIds($paymentMethodId);

    /**
     * Return a list of user ids for which the payment is allowed. Empty array means no restriction.
     *
     * @param string $paymentMethodId
     *
     * @return array
     */
    public function getPermittedUserIds($paymentMethodId);

    /**
     * Return a list of user group ids for which the payment is allowed. Empty array means no restriction.
     *
     * @param string $paymentMethodId
     *
     * @return array
     */
    public function getPermittedUserGroupIds($paymentMethodId);

    /**
     * Return a list of product ids for which the payment is allowed. Empty array means no restriction.
     *
     * @param string $paymentMethodId
     *
     * @return array
     */
    public function getPermittedArticleIds($paymentMethodId);

    /**
     * Return a list of category ids for which the payment is allowed. Empty array means no restriction.
     *
     * @param string $paymentMethodId
     *
     * @return array
     */
    public function getPermittedCategoryIds($paymentMethodId);

    /**
     * Return a list of product group ids for which the payment is allowed. Empty array means no restriction.
     *
     * @param string $paymentMethodId
     *
     * @return array
     */
    public function getPermittedArticleGroupIds($paymentMethodId);

    /**
     * Return a list of product ids for which the payment is not allowed. Empty array means no restriction.
     *
     * @param string $paymentMethodId
     *
     * @return array
     */
    public function getInvalidArticleIds($paymentMethodId);

    /**
     * Return a list of category ids for which the payment is not allowed. Empty array means no restriction.
     *
     * @param string $paymentMethodId
     *
     * @return array
     */
    public function getInvalidCategoryIds($paymentMethodId);

    /**
     * Return a list of article group ids for which the payment is not allowed. Empty array means no restriction.
     *
     * @param string $paymentMethodId
     *
     * @return array
     */
    public function getInvalidArticleGroupIds($paymentMethodId);
}
