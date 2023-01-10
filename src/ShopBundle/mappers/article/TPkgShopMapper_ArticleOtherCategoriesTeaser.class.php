<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;

class TPkgShopMapper_ArticleOtherCategoriesTeaser extends AbstractPkgShopMapper_Article
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
        $oActiveCategory = $this->getShopService()->getActiveCategory();

        $aLinks = array();
        $oCategoryList = $this->getCategoryList($oArticle);
        while ($oCategory = $oCategoryList->Next()) {
            if ($oCategory->id !== $oActiveCategory->id) {
                $aLinks[] = $this->getLink($oCategory, $oCacheTriggerManager, $bCachingEnabled);
            }
        }

        $oVisitor->SetMappedValue('sHeadline', $this->getHeadline());
        $oVisitor->SetMappedValue('aLinks', $aLinks);
    }

    /**
     * return category list for article or parent article if article is variant and fetched category list is empty.
     *
     * @param TdbShopArticle $oArticle
     *
     * @return TdbShopCategoryList
     */
    protected function getCategoryList(TdbShopArticle $oArticle)
    {
        /** @var $oCategoryList TdbShopCategoryList */
        $oCategoryList = $oArticle->GetFieldShopCategoryList();
        $oCategoryList->GoToStart();
        if (0 === $oCategoryList->Length() && $oArticle->IsVariant()) {
            $oParent = $oArticle->GetFieldVariantParent();
            $oCategoryList = $oParent->GetFieldShopCategoryList();
            $oCategoryList->GoToStart();
        }

        return $oCategoryList;
    }

    /**
     * converts the category into an array with link data.
     *
     * @param TdbShopCategory               $oCategory
     * @param IMapperCacheTriggerRestricted $oCacheTriggerManager
     * @param bool                          $bCachingEnabled
     * @param bool                          $bLinkOnlyLastItem    set to true if only the last category should have a link set otherwise each item has a link set
     *
     * @return array
     */
    protected function getLink(TdbShopCategory $oCategory, IMapperCacheTriggerRestricted $oCacheTriggerManager, $bCachingEnabled, $bLinkOnlyLastItem = false)
    {
        $aLinkParts = array();

        $oBreadcrumb = $oCategory->GetBreadcrumb();
        /** @var $oBreadcrumbItem TdbShopCategory */
        while ($oBreadcrumbItem = $oBreadcrumb->Next()) {
            if ($bCachingEnabled) {
                $oCacheTriggerManager->addTrigger($oBreadcrumbItem->table, $oBreadcrumbItem->id);
            }
            $aLinkData = array(
                'sName' => $oBreadcrumbItem->GetName(),
                'sLink' => '',
            );

            if (false === $bLinkOnlyLastItem || (true === $bLinkOnlyLastItem && $oBreadcrumb->IsLast())) {
                $aLinkData['sLink'] = $oBreadcrumbItem->GetLink();
            }
            $aLinkParts[] = $aLinkData;
        }

        return $aLinkParts;
    }

    /**
     * returns headline in the correct language (translated).
     *
     * @return string
     */
    protected function getHeadline()
    {
        return TGlobal::Translate('chameleon_system_shop.text.other_categories_headline');
    }

    /**
     * @return ShopServiceInterface
     */
    private function getShopService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service');
    }
}
