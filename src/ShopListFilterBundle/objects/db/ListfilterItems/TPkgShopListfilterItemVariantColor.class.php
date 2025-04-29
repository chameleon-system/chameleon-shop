<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * base class used to select from a specific variant type.
/**/
class TPkgShopListfilterItemVariantColor extends TPkgShopListfilterItemVariant
{
    /**
     * @param array<string, mixed> $aOptions
     * @return void
     */
    protected function OrderOptions(array &$aOptions): void
    {
        // get the variant type based on the first value
        if (count($aOptions) > 0) {
            $connection = $this->getDatabaseConnection();
            $aTmpOption = array_map(static function (string $value) use ($connection) {
                return $connection->quote($value);
            }, array_keys($aOptions));

            $sKey = $aTmpOption[0];

            $quotedVariantTypeIdentifier = $connection->quote($this->sVariantTypeIdentifier);

            $query = "SELECT `shop_variant_type`.*
                  FROM `shop_variant_type_value`
            INNER JOIN `shop_variant_type` ON `shop_variant_type_value`.`shop_variant_type_id` = `shop_variant_type`.`id`
                 WHERE `shop_variant_type_value`.`name` = {$sKey}
                   AND `shop_variant_type`.`identifier` = {$quotedVariantTypeIdentifier}
                 ";
            $aType = $connection->fetchAssociative($query);

            if ($aType) {
                $quotedFieldName = $connection->quoteIdentifier($aType['shop_variant_type_value_cmsfieldname']);
                $query = "SELECT `shop_variant_type_value`.`name`, `shop_variant_type_value`.`name_grouped`
                      FROM `shop_variant_type_value`
                INNER JOIN `shop_variant_type` ON `shop_variant_type_value`.`shop_variant_type_id` = `shop_variant_type`.`id`
                     WHERE `shop_variant_type`.`identifier` = {$quotedVariantTypeIdentifier}
                       OR `shop_variant_type_value`.`name_grouped` IN (".implode(',', $aTmpOption).")
                  ORDER BY `shop_variant_type_value`.{$quotedFieldName}
                   ";
                $result = $connection->executeQuery($query);
                $aNewOptions = array();
                while ($aRow = $result->fetchAssociative()) {
                    if (!empty($aRow['name_grouped']) && array_key_exists($aRow['name_grouped'], $aOptions)) {
                        $aNewOptions[$aRow['name_grouped']] = $aOptions[$aRow['name_grouped']];
                    } elseif (array_key_exists($aRow['name'], $aOptions)) {
                        $aNewOptions[$aRow['name']] = $aOptions[$aRow['name']];
                    }
                }
                $aOptions = $aNewOptions;
            }
        }
    }}
