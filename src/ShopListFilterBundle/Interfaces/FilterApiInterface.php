<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\pkgshoplistfilter\Interfaces;

use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\ConfigurationInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\ResultFactoryInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateInterface;

interface FilterApiInterface
{
    /**
     * @return string
     */
    public function getArticleListQuery();

    /**
     * @return array
     */
    public function getArticleListFilterRelevantState();

    /**
     * @return bool
     */
    public function allowCache();

    /**
     * @return array
     */
    public function getCacheParameter();

    /**
     * @return array
     */
    public function getCacheTrigger();

    /**
     * @return ResultFactoryInterface
     */
    public function getResultFactory();

    /**
     * @return ConfigurationInterface
     */
    public function getListConfiguration();

    /**
     * @return StateInterface
     */
    public function getArticleListState();
}
