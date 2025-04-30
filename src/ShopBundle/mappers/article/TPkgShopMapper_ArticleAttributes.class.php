<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopMapper_ArticleAttributes extends AbstractPkgShopMapper_Article
{
    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        /** @var $oArticle TdbShopArticle */
        $oArticle = $oVisitor->GetSourceObject('oObject');
        if ($oArticle && $bCachingEnabled) {
            $oCacheTriggerManager->addTrigger($oArticle->table, $oArticle->id);
        }

        $aAttributeList = [];
        $oAttributeList = $oArticle->GetFieldShopAttributeValueList($this->getAttributeOrderBy());
        $aAttributeTypes = [];
        while ($oAttributeValue = $oAttributeList->Next()) {
            if ($bCachingEnabled) {
                $oCacheTriggerManager->addTrigger($oAttributeValue->table, $oAttributeValue->id);
            }
            if ('' != $oAttributeValue->fieldShopAttributeId) {
                if (false === isset($aAttributeTypes[$oAttributeValue->fieldShopAttributeId])) {
                    $oAttribute = $oAttributeValue->GetFieldShopAttribute();
                    if ($oAttribute) {
                        if ($bCachingEnabled) {
                            $oCacheTriggerManager->addTrigger($oAttribute->table, $oAttribute->id);
                        }
                        $aAttributeTypes[$oAttributeValue->fieldShopAttributeId] = $oAttribute;
                    }
                }
                if (false === $aAttributeTypes[$oAttributeValue->fieldShopAttributeId]->fieldIsSystemAttribute) {
                    $aAttributeList[] = [
                        'sName' => (is_object($aAttributeTypes[$oAttributeValue->fieldShopAttributeId])) ? ($aAttributeTypes[$oAttributeValue->fieldShopAttributeId]->fieldName) : (''),
                        'sValue' => $oAttributeValue->fieldName,
                    ];
                }
            }
        }
        $oVisitor->SetMappedValue('aAttributeList', $aAttributeList);
    }

    /**
     * Overwrite this to add your own attribute order.
     *
     * @return string
     */
    protected function getAttributeOrderBy()
    {
        return '`shop_article_shop_attribute_value_mlt`.`entry_sort` ASC';
    }
}
