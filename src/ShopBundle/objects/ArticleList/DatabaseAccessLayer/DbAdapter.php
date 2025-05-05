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
use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\DbAdapterInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\FilterInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\SortTypeInterface;
use Doctrine\DBAL\Connection;

class DbAdapter implements DbAdapterInterface
{
    /**
     * @var Connection
     */
    private $databaseConnection;

    /**
     * @param string $instanceID
     *
     * @return ConfigurationInterface
     */
    public function getConfigurationFromInstanceId($instanceID)
    {
        $configuration = \TdbShopModuleArticleList::GetNewInstance();
        $configuration->LoadFromField('cms_tpl_module_instance_id', $instanceID);

        return $configuration;
    }

    /**
     * @param string $filterId
     *
     * @return Interfaces\FilterDefinitionInterface
     */
    public function getFilterDefinitionFromId($filterId)
    {
        return \TdbShopModuleArticleListFilter::GetNewInstance($filterId);
    }

    /**
     * @return Interfaces\ResultInterface
     */
    public function getListResults(ConfigurationInterface $moduleConfiguration, FilterInterface $filter)
    {
        $query = $filter->getFilterQuery($moduleConfiguration);
        $list = \TdbShopArticleList::GetList($query);

        return new Result($list);
    }

    /**
     * @param string $sortTypeId
     *
     * @return SortTypeInterface
     */
    public function getSortTypeFromId($sortTypeId)
    {
        return \TdbShopModuleArticlelistOrderby::GetNewInstance($sortTypeId);
    }

    /**
     * @return void
     */
    public function setDatabaseConnection(Connection $connection)
    {
        $this->databaseConnection = $connection;
    }

    /**
     * @return Connection
     */
    protected function getDatabaseConnection()
    {
        return $this->databaseConnection;
    }

    /**
     * @param string $configurationId
     *
     * @return array
     */
    public function getSortListForConfiguration($configurationId)
    { // shop_module_article_list_shop_module_articlelist_orderby_mlt
        $sortList = [];
        $query = 'SELECT `shop_module_articlelist_orderby`.*
                    FROM `shop_module_articlelist_orderby`
              INNER JOIN `shop_module_article_list_shop_module_articlelist_orderby_mlt` ON `shop_module_articlelist_orderby`.`id` = `shop_module_article_list_shop_module_articlelist_orderby_mlt`.`target_id`
                   WHERE `shop_module_article_list_shop_module_articlelist_orderby_mlt`.`source_id` = '.$this->getDatabaseConnection()->quote($configurationId).'
                ORDER BY `shop_module_articlelist_orderby`.`position` ASC
        ';
        $list = \TdbShopModuleArticlelistOrderbyList::GetList($query);
        while ($listItem = $list->Next()) {
            $sortList[] = [
                'id' => $listItem->id,
                'name' => $listItem->fieldNamePublic,
            ];
        }

        return $sortList;
    }
}
