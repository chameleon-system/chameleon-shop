<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\pkgshoplistfilter\DatabaseAccessLayer;

use Doctrine\DBAL\Connection;

class DbAdapter
{
    /**
     * @var Connection
     */
    private $databaseConnection;

    /**
     * @param string $pageId
     *
     * @return string|null
     */
    public function getFilterableListInstanceIdOnPage($pageId)
    {
        // find module instances of type x on page with setting y
        $query = "SELECT `shop_module_article_list`.`cms_tpl_module_instance_id`
                    FROM `shop_module_article_list`
              INNER JOIN `cms_tpl_page_cms_master_pagedef_spot` ON `shop_module_article_list`.`cms_tpl_module_instance_id` = `cms_tpl_page_cms_master_pagedef_spot`.`cms_tpl_module_instance_id`
                   WHERE `shop_module_article_list`.`can_be_filtered` = '1'
                     AND `cms_tpl_page_cms_master_pagedef_spot`.`cms_tpl_page_id` = :pageId
                 ";

        /** @psalm-var false|non-empty-list<string> $instanceData */
        $instanceData = $this->getDatabaseConnection()->fetchNumeric($query, array('pageId' => $pageId));
        if (false === $instanceData) {
            return null;
        }

        return $instanceData[0];
    }

    /**
     * @param Connection $connection
     *
     * @return void
     */
    public function setDatabaseConnection(Connection $connection)
    {
        $this->databaseConnection = $connection;
    }

    /**
     * @return object
     */
    protected function getDatabaseConnection()
    {
        if (null !== $this->databaseConnection) {
            return $this->databaseConnection;
        }

        return \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');
    }

    /**
     * @param string $pageId
     *
     * @return string|null
     */
    public function getFilterableListInstanceSpotOnPage($pageId)
    {
        // find module instances of type x on page with setting y
        $query = "SELECT `cms_master_pagedef_spot`.`name`
                    FROM `shop_module_article_list`
              INNER JOIN `cms_tpl_page_cms_master_pagedef_spot` ON `shop_module_article_list`.`cms_tpl_module_instance_id` = `cms_tpl_page_cms_master_pagedef_spot`.`cms_tpl_module_instance_id`
              INNER JOIN `cms_master_pagedef_spot` ON `cms_tpl_page_cms_master_pagedef_spot`.`cms_master_pagedef_spot_id` = `cms_master_pagedef_spot`.`id`
                   WHERE `shop_module_article_list`.`can_be_filtered` = '1'
                     AND `cms_tpl_page_cms_master_pagedef_spot`.`cms_tpl_page_id` = :pageId
                 ";
        $instanceData = $this->getDatabaseConnection()->fetchNumeric($query, array('pageId' => $pageId));
        if (false === $instanceData) {
            return null;
        }

        return $instanceData[0];
    }
}
