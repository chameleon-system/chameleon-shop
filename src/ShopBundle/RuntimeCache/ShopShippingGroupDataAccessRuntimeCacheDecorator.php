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

use ChameleonSystem\ShopBundle\Interfaces\DataAccess\ShopShippingGroupDataAccessInterface;

class ShopShippingGroupDataAccessRuntimeCacheDecorator implements ShopShippingGroupDataAccessInterface
{
    /**
     * @var ShopShippingGroupDataAccessInterface
     */
    private $subject;
    /**
     * @var array
     */
    private $cache = [];

    public function __construct(ShopShippingGroupDataAccessInterface $subject)
    {
        $this->subject = $subject;
    }

    /**
     * {@inheritdoc}
     */
    public function getPermittedUserIds($shippingGroupId)
    {
        $key = sprintf('permittedUserIds-%s', $shippingGroupId);
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }
        $this->cache[$key] = $this->subject->getPermittedUserIds($shippingGroupId);

        return $this->cache[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function getPermittedUserGroupIds($shippingGroupId)
    {
        $key = sprintf('permittedUserGroupIds-%s', $shippingGroupId);
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }
        $this->cache[$key] = $this->subject->getPermittedUserGroupIds($shippingGroupId);

        return $this->cache[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function getPermittedPortalIds($shippingGroupId)
    {
        $key = sprintf('permittedPortalIds-%s', $shippingGroupId);
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }
        $this->cache[$key] = $this->subject->getPermittedPortalIds($shippingGroupId);

        return $this->cache[$key];
    }
}
