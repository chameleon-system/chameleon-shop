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

use ChameleonSystem\ShopBundle\Interfaces\DataAccess\ShopShippingTypeDataAccessInterface;
use TShopBasket;

class ShopShippingTypeDataAccessRuntimeCacheDecorator implements ShopShippingTypeDataAccessInterface
{
    /**
     * @var ShopShippingTypeDataAccessInterface
     */
    private $subject;
    /**
     * @var array
     */
    private $cache = array();

    /**
     * @param ShopShippingTypeDataAccessInterface $subject
     */
    public function __construct(ShopShippingTypeDataAccessInterface $subject)
    {
        $this->subject = $subject;
    }

    /**
     * {@inheritdoc}
     */
    public function getPermittedArticleGroupIds($shippingTypeId)
    {
        $key = sprintf('permittedArticleGroupIds-%s', $shippingTypeId);
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }
        $this->cache[$key] = $this->subject->getPermittedArticleGroupIds($shippingTypeId);

        return $this->cache[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function getPermittedArticleIds($shippingTypeId)
    {
        $key = sprintf('permittedArticleIds-%s', $shippingTypeId);
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }
        $this->cache[$key] = $this->subject->getPermittedArticleIds($shippingTypeId);

        return $this->cache[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function getPermittedCategoryIds($shippingTypeId)
    {
        $key = sprintf('permittedCategoryIds-%s', $shippingTypeId);
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }
        $this->cache[$key] = $this->subject->getPermittedCategoryIds($shippingTypeId);

        return $this->cache[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function getPermittedPortalIds($shippingTypeId)
    {
        $key = sprintf('permittedPortalIds-%s', $shippingTypeId);
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }
        $this->cache[$key] = $this->subject->getPermittedPortalIds($shippingTypeId);

        return $this->cache[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function getPermittedCountryIds($shippingTypeId)
    {
        $key = sprintf('permittedCountryIds-%s', $shippingTypeId);
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }
        $this->cache[$key] = $this->subject->getPermittedCountryIds($shippingTypeId);

        return $this->cache[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function getPermittedUserGroupIds($shippingTypeId)
    {
        $key = sprintf('permittedUserGroupIds-%s', $shippingTypeId);
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }
        $this->cache[$key] = $this->subject->getPermittedUserGroupIds($shippingTypeId);

        return $this->cache[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function getPermittedUserIds($shippingTypeId)
    {
        $key = sprintf('permittedUserIds-%s', $shippingTypeId);
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }
        $this->cache[$key] = $this->subject->getPermittedUserIds($shippingTypeId);

        return $this->cache[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableShippingTypes($shippingGroupId, $shippingCountryId, TShopBasket $basket)
    {
        $parameter = array(
            'iGroupId' => $shippingGroupId,
            'dNumberOfArticles' => $basket->dTotalNumberOfArticles,
            'dWeight' => $basket->dTotalWeight,
            'dBasketVolume' => $basket->dTotalVolume,
            'dBasketValue' => $basket->dCostArticlesTotalAfterDiscounts,
        );
        if ('' !== $shippingCountryId) {
            $parameter['sActiveShippingCountryId'] = $shippingCountryId;
        }

        $key = sprintf('availableShippingTypes-%s', md5(serialize($parameter)));
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }
        $this->cache[$key] = $this->subject->getAvailableShippingTypes($shippingGroupId, $shippingCountryId, $basket);

        return $this->cache[$key];
    }
}
