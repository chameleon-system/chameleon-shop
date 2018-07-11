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
 * provide access to the shipping group configuration.
 */
interface ShopShippingGroupDataAccessInterface
{
    /**
     * Returns a list of user ids that may use the group. An empty array means no restriction.
     *
     * @param string $shippingGroupId
     *
     * @return array
     */
    public function getPermittedUserIds($shippingGroupId);

    /**
     * Returns a list of user group ids that may use the group. An empty array means no restriction.
     *
     * @param string $shippingGroupId
     *
     * @return array
     */
    public function getPermittedUserGroupIds($shippingGroupId);

    /**
     * Returns a list of portal ids that may use the group. An empty array means no restriction.
     *
     * @param string $shippingGroupId
     *
     * @return string[]
     */
    public function getPermittedPortalIds($shippingGroupId);
}
