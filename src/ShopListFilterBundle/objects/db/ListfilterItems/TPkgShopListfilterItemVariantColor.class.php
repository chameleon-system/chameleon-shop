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
    protected function OrderOptions(&$aOptions)
    {
        // get the variant type based on the first value
        if (count($aOptions) > 0) {
            $aTmpOption = TTools::MysqlRealEscapeArray(array_keys($aOptions));
            $sKey = $aTmpOption[0];
            $query = "SELECT `shop_variant_type`.*
                    FROM `shop_variant_type_value`
              INNER JOIN `shop_variant_type` ON `shop_variant_type_value`.`shop_variant_type_id` = `shop_variant_type`.`id`
                   WHERE `shop_variant_type_value`.`name` = '".$sKey."'
                     AND `shop_variant_type`.`identifier` = '".MySqlLegacySupport::getInstance()->real_escape_string($this->sVariantTypeIdentifier)."'
                 ";
            if ($aType = MySqlLegacySupport::getInstance()->fetch_assoc(MySqlLegacySupport::getInstance()->query($query))) {
                $query = "SELECT `shop_variant_type_value`.`name` , `shop_variant_type_value`.`name_grouped`
                      FROM `shop_variant_type_value`
                INNER JOIN `shop_variant_type` ON `shop_variant_type_value`.`shop_variant_type_id` = `shop_variant_type`.`id`
                     WHERE `shop_variant_type`.`identifier` = '".MySqlLegacySupport::getInstance()->real_escape_string($this->sVariantTypeIdentifier)."'
                        OR `shop_variant_type_value`.`name_grouped` IN ('".implode("','", $aTmpOption)."')
                  ORDER BY `shop_variant_type_value`.`".MySqlLegacySupport::getInstance()->real_escape_string($aType['shop_variant_type_value_cmsfieldname']).'`
                   ';
                $tRes = MySqlLegacySupport::getInstance()->query($query);
                $aNewOptions = array();
                while ($aRow = MySqlLegacySupport::getInstance()->fetch_assoc($tRes)) {
                    if (!empty($aRow['name_grouped']) && array_key_exists($aRow['name_grouped'], $aOptions)) {
                        $aNewOptions[$aRow['name_grouped']] = $aOptions[$aRow['name_grouped']];
                    } elseif (array_key_exists($aRow['name'], $aOptions)) {
                        $aNewOptions[$aRow['name']] = $aOptions[$aRow['name']];
                    }
                }
                $aOptions = $aNewOptions;
            }
        }
    }
}
