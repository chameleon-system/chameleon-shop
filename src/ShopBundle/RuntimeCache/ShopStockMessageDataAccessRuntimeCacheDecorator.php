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

use ChameleonSystem\ShopBundle\Interfaces\DataAccess\ShopStockMessageDataAccessInterface;
use TdbShopStockMessage;

class ShopStockMessageDataAccessRuntimeCacheDecorator implements ShopStockMessageDataAccessInterface
{
    /**
     * @var ShopStockMessageDataAccessInterface
     */
    private $subject;
    /**
     * @var array
     */
    private $cache = array();

    /**
     * @param ShopStockMessageDataAccessInterface $subject
     */
    public function __construct(ShopStockMessageDataAccessInterface $subject)
    {
        $this->subject = $subject;
    }

    /**
     * {@inheritdoc}
     */
    public function getStockMessage($id, $languageId)
    {
        $cache = $this->getAll();
        if (isset($cache[$id])) {
            return TdbShopStockMessage::GetNewInstance($cache[$id], $languageId);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getAll()
    {
        $key = 'all';
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }
        $this->cache[$key] = $this->subject->getAll();

        return $this->cache[$key];
    }
}
