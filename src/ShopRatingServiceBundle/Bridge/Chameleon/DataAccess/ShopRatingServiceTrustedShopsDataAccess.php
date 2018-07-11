<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopRatingServiceBundle\Bridge\Chameleon\DataAccess;

use Doctrine\DBAL\Connection;
use ChameleonSystem\ShopRatingService\DataAccess\ShopRatingServiceTrustedShopsDataAccessInterface;

class ShopRatingServiceTrustedShopsDataAccess implements ShopRatingServiceTrustedShopsDataAccessInterface
{
    /**
     * @var Connection
     */
    private $databaseConnection;

    public function __construct(Connection $databaseConnection)
    {
        $this->databaseConnection = $databaseConnection;
    }

    public function getItemCountForRemoteKey($remoteKey)
    {
        $itemCountQuery = 'SELECT COUNT(*) AS item_count FROM pkg_shop_rating_service_rating WHERE remote_key = :remoteKey';
        $itemCount = $this->databaseConnection->fetchColumn($itemCountQuery, array(
            'remoteKey' => $remoteKey,
        ));

        return intval($itemCount);
    }

    public function insertItem(array $data)
    {
        $insertQuery = 'INSERT INTO pkg_shop_rating_service_rating
                               SET id = :insertId,
                                   pkg_shop_rating_service_id = :pkgShopRatingServiceId,
                                   remote_key = :remoteKey,
                                   score = :score,
                                   rawdata = :rawData,
                                   rating_user = :ratingUser,
                                   rating_text = :ratingText,
                                   rating_date = :ratingDate
            ';
        $statement = $this->databaseConnection->prepare($insertQuery);
        $statement->execute($data);
    }
}
