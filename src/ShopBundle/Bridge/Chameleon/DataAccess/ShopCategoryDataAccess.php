<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\Bridge\Chameleon\DataAccess;

use ChameleonSystem\ShopBundle\Interfaces\DataAccess\ShopCategoryDataAccessInterface;
use Doctrine\DBAL\Connection;

class ShopCategoryDataAccess implements ShopCategoryDataAccessInterface
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllActive()
    {
        $activeRestriction = $this->getActiveRestriction();
        $query = 'SELECT * FROM `shop_category`';
        if ('' !== $activeRestriction) {
            $query = $query.' WHERE '.$activeRestriction;
        }
        $query = $query.' ORDER BY `position`';
        $categories = $this->connection->fetchAllAssociative($query);

        $idList = \array_column($categories, 'id');

        return \array_combine($idList, $categories);
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveChildren($categoryId)
    {
        $query = 'SELECT * FROM `shop_category` WHERE `shop_category_id` = :parentId %s ORDER BY `position`';
        $activeRestriction = $this->getActiveRestriction();
        if ('' !== $activeRestriction) {
            $activeRestriction = ' AND '.$activeRestriction;
        }
        $query = sprintf($query, $activeRestriction);

        return $this->connection->fetchAllAssociative($query, ['parentId' => $categoryId]);
    }

    /**
     * {@inheritdoc}
     */
    public function getCategory($categoryId)
    {
        $query = 'SELECT * FROM `shop_category` WHERE `shop_category_id` = :categoryId';
        $row = $this->connection->fetchAllAssociative($query, ['categoryId' => $categoryId]);
        if (false === $row) {
            return null;
        }

        return $row;
    }

    /**
     * @return string
     */
    private function getActiveRestriction()
    {
        return \TdbShopCategoryList::GetActiveCategoryQueryRestriction();
    }
}
