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

interface DbAdapterInterface
{
    /**
     * @param string $instanceID
     *
     * @return ConfigurationInterface
     */
    public function getConfigurationFromInstanceId($instanceID);

    /**
     * @param string $filterId
     *
     * @return FilterDefinitionInterface
     */
    public function getFilterDefinitionFromId($filterId);

    /**
     * @param ConfigurationInterface $moduleConfiguration
     * @param FilterInterface        $filter
     *
     * @return ResultInterface
     */
    public function getListResults(ConfigurationInterface $moduleConfiguration, FilterInterface $filter);

    /**
     * @param string $sortTypeId
     *
     * @return SortTypeInterface
     */
    public function getSortTypeFromId($sortTypeId);

    /**
     * @param string $configurationId
     *
     * @return array
     */
    public function getSortListForConfiguration($configurationId);
}
