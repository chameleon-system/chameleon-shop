<?php

namespace ChameleonSystem\ShopRevocationBundle\Service;

use ChameleonSystem\ShopRevocationBundle\DataAccess\ShopRevocationOrderSelectionDataAccess;
use ChameleonSystem\ShopRevocationBundle\DataModel\SelectableOrderDataModel;

class ShopRevocationOrderSelectionService
{
    private const RESULT_LIMIT = 25;
    private const QUERY_WINDOW_MONTHS = 3;

    public function __construct(
        private readonly ShopRevocationOrderSelectionDataAccess $dataAccess,
        private readonly ShopRevocationOrderRelevanceService $orderRelevanceService
    ) {
    }

    /**
     * @return array<int, array{sValue: string, sName: string}>
     */
    public function getSelectableOrders(string $shopId, string $extranetUserId): array
    {
        $oldestOrderDate = new \DateTimeImmutable(sprintf('-%d months', self::QUERY_WINDOW_MONTHS));
        $candidates = $this->dataAccess->getSelectableOrderCandidates($shopId, $extranetUserId, $oldestOrderDate->format('Y-m-d H:i:s'));
        $selectableOrders = [];

        foreach ($candidates as $candidate) {
            $order = new \TShopOrder();
            if (false === $order->Load($candidate->getId())) {
                continue;
            }

            if (false === $this->orderRelevanceService->isOrderRelevantForRevocation($order)) {
                continue;
            }

            $selectableOrders[] = new SelectableOrderDataModel(
                sprintf(
                    '%s (%s)',
                    $candidate->getOrderNumber(),
                    $this->formatOrderDate($candidate->getDateCreated())
                ),
                $candidate->getId()
            );
        }

        return \array_map(
            static fn (SelectableOrderDataModel $selectableOrder): array => $selectableOrder->toArray(),
            \array_slice($selectableOrders, 0, self::RESULT_LIMIT)
        );
    }

    private function formatOrderDate(string $dateCreated): string
    {
        try {
            return (new \DateTimeImmutable($dateCreated))->format('d.m.Y');
        } catch (\Exception) {
            return $dateCreated;
        }
    }
}
