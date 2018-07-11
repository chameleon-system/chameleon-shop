<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopCurrencyBundle\Service;

use ChameleonSystem\ShopCurrencyBundle\Interfaces\ShopCurrencyServiceInterface;

class ShopCurrencyServiceRequestLevelCacheDecorator implements ShopCurrencyServiceInterface
{
    /**
     * @var array
     */
    private $cache = array();
    /**
     * @var ShopCurrencyServiceInterface
     */
    private $subject;

    /**
     * @param ShopCurrencyServiceInterface $subject
     */
    public function __construct(ShopCurrencyServiceInterface $subject)
    {
        $this->subject = $subject;
    }

    /**
     * {@inheritdoc}
     */
    public function getSymbol()
    {
        $cacheKey = 'getSymbol';
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $value = $this->subject->getSymbol();

        if (true === $this->allowCache()) {
            $this->cache[$cacheKey] = $value;
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getIso4217Code()
    {
        $cacheKey = 'getIso4217Code';
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $value = $this->subject->getIso4217Code();

        if (true === $this->allowCache()) {
            $this->cache[$cacheKey] = $value;
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function formatNumber($value)
    {
        return $this->subject->formatNumber($value);
    }

    public function reset()
    {
        $this->subject->reset();
        $this->cache = array();
    }

    /**
     * {@inheritdoc}
     */
    public function getObject()
    {
        $cacheKey = 'getObject';
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $value = $this->subject->getObject();
        if (true === $this->allowCache()) {
            $this->cache[$cacheKey] = $value;
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveCurrencyId($bUseDefaultIfNotDefinedForUser = true)
    {
        $cacheKey = "getActiveCurrencyId-$bUseDefaultIfNotDefinedForUser";
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $value = $this->subject->getActiveCurrencyId();
        if (null !== $value) {
            $this->cache[$cacheKey] = $value;
        }

        return $value;
    }

    /**
     * @return bool
     */
    private function allowCache()
    {
        return null !== $this->getActiveCurrencyId();
    }
}
