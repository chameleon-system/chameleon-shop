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

use ChameleonSystem\ShopBundle\Interfaces\DataAccess\ShopStockMessageDataAccessInterface;
use Doctrine\DBAL\Connection;
use TdbShopStockMessage;

class ShopStockMessageDataAccess implements ShopStockMessageDataAccessInterface
{
    /**
     * @var Connection
     */
    private $databaseConnection;

    /**
     * @param Connection $databaseConnection
     */
    public function __construct(Connection $databaseConnection)
    {
        $this->databaseConnection = $databaseConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function getStockMessage($id, $languageId)
    {
        $message = TdbShopStockMessage::GetNewInstance($id, $languageId);

        if (false === $message->sqlData) {
            return null;
        }

        return $message;
    }

    /**
     * {@inheritdoc}
     */
    public function getAll()
    {
        $rows = $this->databaseConnection->fetchAllAssociative('SELECT * FROM `shop_stock_message`');

        return array_reduce(
            $rows,
            function (array $carry, array $row) {
                $carry[$row['id']] = $row;

                return $carry;
            },
            array()
        );
    }
}
