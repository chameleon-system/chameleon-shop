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

use ChameleonSystem\ShopBundle\Interfaces\DataAccess\ShopShippingGroupDataAccessInterface;
use Doctrine\DBAL\Connection;

class ShopShippingGroupDataAccess implements ShopShippingGroupDataAccessInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function getPermittedUserIds($shippingGroupId)
    {
        return $this->getIdsFromMlt('shop_shipping_group_data_extranet_user_mlt', $shippingGroupId);
    }

    /**
     * {@inheritdoc}
     */
    public function getPermittedUserGroupIds($shippingGroupId)
    {
        return $this->getIdsFromMlt('shop_shipping_group_data_extranet_group_mlt', $shippingGroupId);
    }

    /**
     * {@inheritdoc}
     */
    public function getPermittedPortalIds($shippingGroupId)
    {
        return $this->getIdsFromMlt('shop_shipping_group_cms_portal_mlt', $shippingGroupId);
    }

    /**
     * @param string $mltName
     * @param string $shippingGroupId
     *
     * @return array
     */
    private function getIdsFromMlt($mltName, $shippingGroupId)
    {
        $query = sprintf('SELECT %1$s.`target_id`
                    FROM %1$s
                   WHERE %1$s.`source_id` = :shippingGroupId
                    ', $this->connection->quoteIdentifier($mltName));
        $idRows = $this->connection->fetchAllAssociative($query, array('shippingGroupId' => $shippingGroupId));

        return array_map(
            function (array $row) {
                return $row['target_id'];
            },
            $idRows
        );
    }
}
