<?php

namespace ChameleonSystem\ShopRevocationBundle\Service;

use ChameleonSystem\ShopRevocationBundle\DataModel\RevocationFormDataModel;

class ShopRevocationPersistenceService
{
    private const TABLE_NAME = 'shop_order_revocation';

    public function createRevocation(RevocationFormDataModel $formData, \TShopOrder $order, \DateTimeImmutable $submittedAt): string
    {
        $tableEditor = new \TCMSTableEditorManager();
        $tableEditor->Init(\TTools::GetCMSTableId(self::TABLE_NAME), null);
        $tableEditor->AllowEditByAll(true);

        try {
            $recordData = $tableEditor->SaveFields([
                'shop_order_id' => (string) $order->id,
                'name' => $formData->getName(),
                'email' => $formData->getEmail(),
                'customer_note' => $formData->getCustomerNote(),
                'revocation_datetime' => $submittedAt->format('Y-m-d H:i:s'),
            ], true);
        } finally {
            $tableEditor->AllowEditByAll(false);
        }

        $recordId = (string) ($recordData->id ?? '');
        if ('' === $recordId) {
            throw new \RuntimeException('Failed to create shop order revocation record.');
        }

        return $recordId;
    }
}
