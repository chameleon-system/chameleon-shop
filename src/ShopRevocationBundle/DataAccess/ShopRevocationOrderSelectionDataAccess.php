<?php

namespace ChameleonSystem\ShopRevocationBundle\DataAccess;

use ChameleonSystem\ShopRevocationBundle\DataModel\SelectableOrderCandidateDataModel;
use Doctrine\DBAL\Connection;

class ShopRevocationOrderSelectionDataAccess
{
    private const CANDIDATE_LIMIT = 25;

    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * @return array<int, SelectableOrderCandidateDataModel>
     */
    public function getSelectableOrderCandidates(string $shopId, string $extranetUserId, string $cutoffDate): array
    {
        $query = 'SELECT
                `id`,
                `ordernumber`,
                `datecreated`
             FROM `shop_order`
             WHERE `shop_id` = :shopId
               AND `data_extranet_user_id` = :extranetUserId
               AND `canceled` = :canceled
               AND `datecreated` >= :cutoffDate
             ORDER BY `datecreated` DESC
             LIMIT '.self::CANDIDATE_LIMIT;

        $rows = $this->connection->fetchAllAssociative($query, [
            'shopId' => $shopId,
            'extranetUserId' => $extranetUserId,
            'canceled' => '0',
            'cutoffDate' => $cutoffDate,
        ]);

        return \array_map(
            static fn (array $row): SelectableOrderCandidateDataModel => new SelectableOrderCandidateDataModel(
                (string) $row['id'],
                (string) $row['ordernumber'],
                (string) $row['datecreated']
            ),
            $rows
        );
    }
}
