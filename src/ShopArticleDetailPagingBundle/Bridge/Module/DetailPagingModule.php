<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopArticleDetailPagingBundle\Bridge\Module;

use ChameleonSystem\ShopArticleDetailPagingBundle\Exception\ArticleListException;
use ChameleonSystem\ShopArticleDetailPagingBundle\Interfaces\DetailPagingServiceInterface;
use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;
use IMapperCacheTriggerRestricted;
use IMapperVisitorRestricted;

class DetailPagingModule extends \MTPkgViewRendererAbstractModuleMapper
{
    /**
     * @var \ChameleonSystem\ShopArticleDetailPagingBundle\Interfaces\DetailPagingServiceInterface
     */
    private $detailPagingService = null;

    /** @var string|null */
    private $activeProductId = null;

    public function __construct(ShopServiceInterface $shop, DetailPagingServiceInterface $detailPagingService)
    {
        $this->detailPagingService = $detailPagingService;
        $activeProduct = $shop->getActiveProduct();
        if ($activeProduct) {
            $this->activeProductId = $activeProduct->id;
        }
    }

    public function Init()
    {
        parent::Init();
    }

    public function _AllowCache()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(
        IMapperVisitorRestricted $oVisitor,
        $bCachingEnabled,
        IMapperCacheTriggerRestricted $oCacheTriggerManager
    ) {
        try {
            $data = array(
                'backToListUrl' => $this->detailPagingService->getBackToListUrl($this->sModuleSpotName),
                'nextItem' => $this->detailPagingService->getNextItem($this->activeProductId, $this->sModuleSpotName),
                'previousItem' => $this->detailPagingService->getPreviousItem($this->activeProductId, $this->sModuleSpotName),
            );

            $oVisitor->SetMappedValueFromArray($data);
        } catch (ArticleListException $e) {
            // ignore this exception for now. Throw some kind of ModuleException once this concept exists.
        }
    }
}
