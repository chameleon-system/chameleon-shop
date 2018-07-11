<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer;

use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\ConfigurationInterface;

class Configuration extends \ChameleonSystemShopBundleobjectsArticleListDatabaseAccessLayerConfigurationAutoParent implements ConfigurationInterface
{
    /**
     * returns null if there is no page size set.
     *
     * @return int|null
     */
    public function getDefaultPageSize()
    {
        return $this->fieldNumberOfArticlesPerPage;
    }

    /**
     * @return string
     */
    public function getDefaultSortId()
    {
        return $this->fieldShopModuleArticlelistOrderbyId;
    }

    public function getDefaultFilterId()
    {
        return $this->fieldShopModuleArticleListFilterId;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getMaxResultLimitation()
    {
        return ($this->fieldNumberOfArticles > 0) ? (int) $this->fieldNumberOfArticles : null;
    }

    /**
     * @return \TdbShopModuleArticleList
     */
    public function getDatabaseObject()
    {
        return $this;
    }

    public function getAsArray()
    {
        return $this->sqlData;
    }
}
