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

use TShopBasket;

/**
 * access shipping type configuration.
 */
interface ShopShippingTypeDataAccessInterface
{
    /**
     * Returns a list of article group ids that may use the shipping type. An empty array means no restriction.
     *
     * @param string $shippingTypeId
     *
     * @return string[]
     */
    public function getPermittedArticleGroupIds($shippingTypeId);

    /**
     * Returns a list of article ids that may use the shipping type. An empty array means no restriction.
     *
     * @param string $shippingTypeId
     *
     * @return string[]
     */
    public function getPermittedArticleIds($shippingTypeId);

    /**
     * Returns a list of category ids that may use the shipping type. An empty array means no restriction.
     *
     * @param string $shippingTypeId
     *
     * @return string[]
     */
    public function getPermittedCategoryIds($shippingTypeId);

    /**
     * Returns a list of portal ids that may use the shipping type. An empty array means no restriction.
     *
     * @param string $shippingTypeId
     *
     * @return string[]
     */
    public function getPermittedPortalIds($shippingTypeId);

    /**
     * Returns a list of country ids that may use the shipping type. An empty array means no restriction.
     *
     * @param string $shippingTypeId
     *
     * @return string[]
     */
    public function getPermittedCountryIds($shippingTypeId);

    /**
     * Returns a list of user group ids that may use the shipping type. An empty array means no restriction.
     *
     * @param string $shippingTypeId
     *
     * @return string[]
     */
    public function getPermittedUserGroupIds($shippingTypeId);

    /**
     * Returns a list of user ids that may use the shipping type. An empty array means no restriction.
     *
     * @param string $shippingTypeId
     *
     * @return string[]
     */
    public function getPermittedUserIds($shippingTypeId);

    /**
     * Returns a list of active shipping types that may use the shipping type.
     *
     * @param string      $shippingGroupId
     * @param string      $shippingCountryId
     * @param TShopBasket $basket
     *
     * @return array
     */
    public function getAvailableShippingTypes($shippingGroupId, $shippingCountryId, TShopBasket $basket);
}
