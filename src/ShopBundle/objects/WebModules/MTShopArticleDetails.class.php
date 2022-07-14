<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\PortalDomainServiceInterface;
use ChameleonSystem\ExtranetBundle\Interfaces\ExtranetUserProviderInterface;
use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MTShopArticleDetails extends MTPkgViewRendererAbstractModuleMapper
{
    /**
     * @var string|null
     */
    private $sActiveImageId = null;

    /**
     * @var array
     */
    private $aVariantTypeValues = array();

    /**
     * @var TdbShopArticle
     */
    private $oActiveArticle = null;
    /**
     * @var TdbShopCategory
     */
    private $oActiveCategory;

    /**
     * {@inheritdoc}
     */
    public function Init()
    {
        parent::Init();
        if ($this->global->UserDataExists('imageid')) {
            $this->sActiveImageId = $this->global->GetUserData('imageid');
        }
        if ($this->global->UserDataExists(\TShopVariantType::URL_PARAMETER)) {
            $this->aVariantTypeValues = $this->global->GetUserData(\TShopVariantType::URL_PARAMETER);
        }
        $shopService = $this->getShopService();
        $this->oActiveArticle = $shopService->getActiveProduct();
        $this->oActiveCategory = $shopService->getActiveCategory();
        if (null === $this->oActiveArticle) {
            if (null === $this->getPortalDomainService()->getActivePortal()) {
                $this->oActiveArticle = TdbShopArticle::GetNewInstance();
            } else {
                throw new NotFoundHttpException();
            }
        }
        $this->OneTimeActiveArticleActions();
    }

    /**
     * @return void
     */
    protected function OneTimeActiveArticleActions()
    {
        if (!defined('ARTICLE_DETAIL_ONE_TIME_ACTION') || (defined('ARTICLE_DETAIL_ONE_TIME_ACTION') && ARTICLE_DETAIL_ONE_TIME_ACTION === false)) {
            define('ARTICLE_DETAIL_ONE_TIME_ACTION', true);
            $oActiveArticle = $this->getShopService()->getActiveProduct();
            if (is_object($oActiveArticle)) {
                $sAddItemId = $oActiveArticle->id;
                if ($oActiveArticle->IsVariant()) {
                    $sAddItemId = $oActiveArticle->fieldVariantParentId;
                }
                $oUser = $this->getExtranetUserProvider()->getActiveUser();
                $oUser->AddArticleIdToViewHistory($sAddItemId);
                $oActiveArticle->UpdateProductViewCount();
            }
        }
    }

    public function GetRequirements(IMapperRequirementsRestricted $oRequirements)
    {
        parent::GetRequirements($oRequirements);
        $oRequirements->NeedsSourceObject('oObject', 'TdbShopArticle', $this->oActiveArticle);
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        /**
         * @var $activeArticle TdbShopArticle
         */
        $activeArticle = $oVisitor->GetSourceObject('oObject');

        $oVisitor->SetMappedValue('oObject', $activeArticle);
        $oVisitor->SetMappedValue('activeArticleUrl', $activeArticle->getLink(true));
        $oVisitor->SetMappedValue('sActiveImageId', $this->sActiveImageId);
        if ($this->oActiveArticle && $bCachingEnabled) {
            $oCacheTriggerManager->addTrigger('shop_article', $this->oActiveArticle->id);
            if ($this->oActiveArticle->IsVariant()) {
                $oCacheTriggerManager->addTrigger('shop_article', $this->oActiveArticle->fieldVariantParentId);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function _GetCacheParameters()
    {
        $cacheParam = parent::_GetCacheParameters();

        $activePortal = $this->getPortalDomainService()->getActivePortal();
        $cacheParam['cms_portal_id'] = $activePortal->id;
        if ($this->oActiveArticle) {
            $cacheParam['sArticleId'] = $this->oActiveArticle->id;
        }
        if ($this->oActiveCategory) {
            $cacheParam['sActiveCategoryId'] = $this->oActiveCategory->id;
        }
        $cacheParam['variantselection'] = $this->aVariantTypeValues;
        $cacheParam['sActiveImageId'] = $this->sActiveImageId;

        return $cacheParam;
    }

    /**
     * {@inheritdoc}
     */
    public function _AllowCache()
    {
        return true;
    }

    /**
     * @return ExtranetUserProviderInterface
     */
    private function getExtranetUserProvider()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_extranet.extranet_user_provider');
    }

    /**
     * @return ShopServiceInterface
     */
    private function getShopService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service');
    }

    /**
     * @return PortalDomainServiceInterface
     */
    private function getPortalDomainService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.portal_domain_service');
    }
}
