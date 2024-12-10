<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\objects\ArticleList\Mapper;

use AbstractViewMapper;
use IMapperCacheTriggerRestricted;
use IMapperRequirementsRestricted;
use IMapperVisitorRestricted;
use MapperException;
use TdbShopArticle;

class ItemMapperNoticeList extends AbstractViewMapper
{
    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements): void
    {
        $oRequirements->NeedsSourceObject('itemMapperBaseData', 'array');
        $oRequirements->NeedsSourceObject('items', 'array');
        $oRequirements->NeedsSourceObject('shop', 'TdbShop');
        $oRequirements->NeedsSourceObject('local', 'TdbCmsLocals');
        $oRequirements->NeedsSourceObject('currency', 'TdbPkgShopCurrency');
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(
        IMapperVisitorRestricted $oVisitor,
        $bCachingEnabled,
        IMapperCacheTriggerRestricted $oCacheTriggerManager
    ): void {
        /** @var TdbShopArticle[] $items */
        $items = $oVisitor->GetSourceObject('items');

        $itemMapperBaseData = $oVisitor->GetSourceObject('itemMapperBaseData');
        $additionalParameter = array(
            'oShop' => $oVisitor->GetSourceObject('shop'),
            'oLocal' => $oVisitor->GetSourceObject('local'),
            'oCurrency' => $oVisitor->GetSourceObject('currency'),
        );
        $additionalParameter = array_merge($itemMapperBaseData, $additionalParameter);
        $oVisitor->SetMappedValue('itemsMappedData', $this->mapItems($oVisitor, $items, $additionalParameter));
        $oVisitor->SetMappedValue('legacyItemView', '/common/teaser/notice-with-hover.html.twig');
    }

    /**
     * @param IMapperVisitorRestricted $visitor
     * @param TdbShopArticle[]         $itemList
     * @param array                    $input
     *
     * @return array
     *
     * @throws MapperException
     */
    private function mapItems(IMapperVisitorRestricted $visitor, array $itemList, array $input)
    {
        $input['oObject'] = null;
        $newItemList = array();
        foreach ($itemList as $item) {
            $input['oObject'] = $item;
            $newItemList[] = $visitor->runMapperChainOn('item.noticelist', $input);
        }

        return $newItemList;
    }
}
