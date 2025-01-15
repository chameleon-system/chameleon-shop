<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Routing\PortalAndLanguageAwareRouterInterface;
use ChameleonSystem\CoreBundle\ServiceLocator;

class TPkgShopMapper_ArticleImageGallery extends AbstractPkgShopMapper_Article
{
    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements): void
    {
        parent::GetRequirements($oRequirements);
        $oRequirements->NeedsSourceObject('sActiveImageId', 'string', null, true);
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager): void
    {
        /** @var $oShop TdbShop */
        $oShop = $oVisitor->GetSourceObject('oShop');
        $oCacheTriggerManager->addTrigger($oShop->table, $oShop->id);

        /** @var $oArticle TdbShopArticle */
        $oArticle = $oVisitor->GetSourceObject('oObject');
        $oCacheTriggerManager->addTrigger($oArticle->table, $oArticle->id);

        $sActiveImageId = $oVisitor->GetSourceObject('sActiveImageId');

        $aActiveItem = [];
        $aImages = [];
        $oImages = $oArticle->GetFieldShopArticleImageList();
        if ($oImages->Length() > 0) {
            while ($oImage = $oImages->Next()) {
                $oCacheTriggerManager->addTrigger($oImage->table, $oImage->id);
                $aImage = $this->getImageDetails($oArticle, $oImage, $oCacheTriggerManager);
                if (0 === count($aActiveItem) && ($sActiveImageId == $oImage->id || null === $sActiveImageId)) {
                    $aActiveItem = $aImage;
                }
                $aImages[] = $aImage;
            }
        } else {
            $oImage = $oArticle->GetImagePreviewObject('standard-list');
            if ($oImage) {
                $oCacheTriggerManager->addTrigger($oImage->table, $oImage->id);
                $aActiveItem = $this->getImageDetails($oArticle, $oImage, $oCacheTriggerManager);
            }
        }
        $oVisitor->SetMappedValue('aItems', $aImages);
        $oVisitor->SetMappedValueFromArray($aActiveItem);
    }

    private function getImageDetails(TdbShopArticle $oArticle, TdbShopArticleImage|TdbShopArticlePreviewImage $oImage, IMapperCacheTriggerRestricted $oCacheTriggerManager): array
    {
        $oCacheTriggerManager->addTrigger('cms_media', $oImage->fieldCmsMediaId);

        $router = $this->getRouter();
        $quickShopLink = $router->generateWithPrefixes('shop_article_quickshop', ['identifier' => $oArticle->sqlData['cmsident']]);
        $quickShopLink .= '?imageid='.$oImage->id;

        return [
            'sImageId' => $oImage->fieldCmsMediaId,
            'sImageTitle' => '',
            'sSelectImageURL' => $quickShopLink,
            'sLargeImageId' => $oImage->fieldCmsMediaId,
            'sLargeTitle' => '',
            'sLargeSelectImageURL' => $quickShopLink,
        ];
    }

    private function getRouter(): PortalAndLanguageAwareRouterInterface
    {
        return ServiceLocator::get('chameleon_system_core.router.chameleon_frontend');
    }
}
