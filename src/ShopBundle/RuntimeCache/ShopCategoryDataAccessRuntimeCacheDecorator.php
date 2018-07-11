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

use ChameleonSystem\ShopBundle\Interfaces\DataAccess\ShopCategoryDataAccessInterface;

class ShopCategoryDataAccessRuntimeCacheDecorator implements ShopCategoryDataAccessInterface
{
    /**
     * root categories do not have a parent id - so we force a key to use in the parentChildMapping.
     */
    const PARENT_CHILD_MAPPING_LOOKUP_FOR_ROOT_NODES = '__root';
    /**
     * @var ShopCategoryDataAccessInterface
     */
    private $subject;
    /**
     * @var array|null
     */
    private $categoryCache;
    /**
     * @var array|null
     */
    private $parentChildMapping;

    /**
     * @param ShopCategoryDataAccessInterface $subject
     */
    public function __construct(ShopCategoryDataAccessInterface $subject)
    {
        $this->subject = $subject;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllActive()
    {
        if (null !== $this->categoryCache) {
            return $this->categoryCache;
        }

        $this->categoryCache = $this->subject->getAllActive();

        return $this->categoryCache;
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveChildren($categoryId)
    {
        $childIds = $this->getChildrenIds($categoryId);
        $children = array();
        foreach ($childIds as $childId) {
            $children[] = $this->getCategory($childId);
        }

        return $children;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategory($categoryId)
    {
        $categoryCache = $this->getAllActive();
        if (isset($categoryCache[$categoryId])) {
            return $categoryCache[$categoryId];
        }

        return null;
    }

    /**
     * @param string $categoryId
     *
     * @return array
     */
    private function getChildrenIds($categoryId)
    {
        $parentChildMapping = $this->getParentChildMapping();
        if ('' === $categoryId) {
            $categoryId = self::PARENT_CHILD_MAPPING_LOOKUP_FOR_ROOT_NODES;
        }
        if (!isset($parentChildMapping[$categoryId])) {
            return array();
        }

        return $parentChildMapping[$categoryId];
    }

    /**
     * @return array
     */
    private function getParentChildMapping()
    {
        if (null !== $this->parentChildMapping) {
            return $this->parentChildMapping;
        }

        $this->parentChildMapping = array();
        $categoryCache = $this->getAllActive();
        foreach (array_keys($categoryCache) as $categoryId) {
            $parent = $categoryCache[$categoryId]['shop_category_id'];
            if ('' === $parent) {
                $parent = self::PARENT_CHILD_MAPPING_LOOKUP_FOR_ROOT_NODES;
            }
            if (!isset($this->parentChildMapping[$parent])) {
                $this->parentChildMapping[$parent] = array();
            }
            $this->parentChildMapping[$parent][] = $categoryId;
        }

        return $this->parentChildMapping;
    }
}
