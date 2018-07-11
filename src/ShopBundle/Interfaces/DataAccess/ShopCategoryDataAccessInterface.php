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
 * access the shop category table.
 */
interface ShopCategoryDataAccessInterface
{
    /**
     * Return a list of all active categories.
     *
     * @return array
     */
    public function getAllActive();

    /**
     * Return all active children of a category.
     *
     * @param string $categoryId
     *
     * @return array
     */
    public function getActiveChildren($categoryId);

    /**
     * Returns a category.
     *
     * @param string $categoryId
     *
     * @return array|null
     */
    public function getCategory($categoryId);
}
