<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces;

use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\ConfigurationInterface;

interface ResultFactoryInterface
{
    /**
     * note: if an invalid page is requested via state, then the first page will be returned instead.
     *
     * @return ResultDataInterface
     */
    public function createResult(ConfigurationInterface $moduleConfiguration, StateInterface $state);

    /**
     * @return bool
     */
    public function _AllowCache(ConfigurationInterface $moduleConfiguration);

    /**
     * @return array
     */
    public function _GetCacheParameters(ConfigurationInterface $moduleConfiguration, StateInterface $state);

    /**
     * @return array
     */
    public function _GetCacheTableInfos(ConfigurationInterface $moduleConfiguration);

    /**
     * @return string
     */
    public function getFilterQuery(ConfigurationInterface $moduleConfiguration);

    /**
     * @return void
     */
    public function moduleInitHook(ConfigurationInterface $moduleConfiguration);
}
