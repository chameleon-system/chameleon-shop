<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces;

interface ConfigurationInterface
{
    /**
     * returns null if there is no page size set.
     *
     * @return int|null
     */
    public function getDefaultPageSize();

    /**
     * @return string
     */
    public function getDefaultSortId();

    public function getDefaultFilterId();

    public function getId();

    /**
     * @return int|null
     */
    public function getMaxResultLimitation();

    /**
     * @return \TdbShopModuleArticleList
     */
    public function getDatabaseObject();

    public function getAsArray();
}
