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

interface FilterInterface
{
    /**
     * @param ConfigurationInterface $moduleConfiguration
     *
     * @return string
     */
    public function getFilterQuery(ConfigurationInterface $moduleConfiguration);

    /**
     * @return bool
     */
    public function _AllowCache();

    /**
     * @return array
     */
    public function _GetCacheParameters();

    /**
     * @return array
     */
    public function _GetCacheTableInfos();

    /**
     * @return bool
     */
    public function PreventUseOfParentObjectWhenNoRecordsAreFound();

    /**
     * @return FilterInterface
     */
    public function getFallbackListFilter();

    /**
     * called when the article list module has completed the initialization.
     */
    public function ModuleInitHook();
}
