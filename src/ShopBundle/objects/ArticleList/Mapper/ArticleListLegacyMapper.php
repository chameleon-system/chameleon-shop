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
 * maps the output of the new list to the legacy views.
 */

namespace ChameleonSystem\ShopBundle\objects\ArticleList\Mapper;

use AbstractViewMapper;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\ResultDataInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateInterface;
use IMapperCacheTriggerRestricted;
use IMapperRequirementsRestricted;
use IMapperVisitorRestricted;
use ViewRenderer;

class ArticleListLegacyMapper extends AbstractViewMapper
{
    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements): void
    {
        $oRequirements->NeedsSourceObject(
            'results',
            '\ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\ResultDataInterface'
        );
        $oRequirements->NeedsSourceObject('items', 'array');
        $oRequirements->NeedsSourceObject('itemsMappedData', 'array');
        $oRequirements->NeedsSourceObject('listPagerUrl', 'string');
        $oRequirements->NeedsSourceObject('numberOfPages', 'int');
        $oRequirements->NeedsSourceObject('state', 'array');
        $oRequirements->NeedsSourceObject('sortList', 'array');
        $oRequirements->NeedsSourceObject('sortFieldName', 'string');
        $oRequirements->NeedsSourceObject('sortFormAction', 'string');
        $oRequirements->NeedsSourceObject('sortFormStateInputFields', 'string');
        $oRequirements->NeedsSourceObject(
            'stateObject',
            '\ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateInterface'
        );
        $oRequirements->NeedsSourceObject(
            'listConfiguration',
            '\ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\ConfigurationInterface'
        );
        $oRequirements->NeedsSourceObject('local', '\TdbCmsLocals');
        $oRequirements->NeedsSourceObject('currency', '\TdbPkgShopCurrency');

        $oRequirements->NeedsSourceObject('legacyItemView', 'string');
        $oRequirements->NeedsSourceObject('sModuleSpotName', 'string');
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(
        IMapperVisitorRestricted $oVisitor,
        $bCachingEnabled,
        IMapperCacheTriggerRestricted $oCacheTriggerManager
    ): void {
        /** @var $results ResultDataInterface */
        $results = $oVisitor->GetSourceObject('results');

        $sortList = $oVisitor->GetSourceObject('sortList');

        $itemsMappedData = $oVisitor->GetSourceObject('itemsMappedData');

        /** @var $stateObject StateInterface */
        $stateObject = $oVisitor->GetSourceObject('stateObject');

        $output = array(
            'sListOptionSort' => $this->getListOptionSort(
                    $sortList,
                    $stateObject->getState(StateInterface::SORT),
                    $oVisitor->GetSourceObject('sortFieldName'),
                    $oVisitor->GetSourceObject('sortFormStateInputFields'),
                    $oVisitor->GetSourceObject('sortFormAction')
                ),
            'oList' => $this->getListProxy(
                    $results,
                    $stateObject->getState(StateInterface::PAGE),
                    $stateObject->getState(StateInterface::PAGE_SIZE),
                    $oVisitor->GetSourceObject('listPagerUrl'),
                    $oVisitor->GetSourceObject('sModuleSpotName')
                ),
            'listIdent' => $oVisitor->GetSourceObject('sModuleSpotName'),
            'aArticleList' => $this->getRenderedItemList($itemsMappedData, $oVisitor->GetSourceObject('legacyItemView')),
            'oLocal' => $oVisitor->GetSourceObject('local'),
            'oCurrency' => $oVisitor->GetSourceObject('currency'),
        );

        $oVisitor->SetMappedValueFromArray($output);
    }

    /**
     * @param array  $sortList
     * @param string $activeOrderById
     * @param string $sortFieldName
     * @param string $sortFormStateInputFields
     * @param string $sortFormAction
     *
     * @return string
     */
    private function getListOptionSort(
        array $sortList,
        $activeOrderById,
        $sortFieldName,
        $sortFormStateInputFields,
        $sortFormAction
    ) {
        $aData = array(
            'sName' => \\ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop.product_list.sort'),
            'sFormActionUrl' => $sortFormAction,
            'sFormId' => '',
            'sSelectName' => $sortFieldName,
            'sFormHiddenFields' => $sortFormStateInputFields,
            'aOptionList' => array(),
        );

        foreach ($sortList as $sortOption) {
            $sortOption['bSelected'] = ($sortOption['id'] === $activeOrderById);
            $aData['aOptionList'][] = array(
                'sValue' => $sortOption['id'],
                'sName' => $sortOption['name'],
                'bSelected' => ($sortOption['id'] === $activeOrderById),
            );
        }

        $oViewRenderer = $this->getViewRenderer();
        $oViewRenderer->AddSourceObjectsFromArray($aData);

        return $oViewRenderer->Render('/common/lists/listOptionCustom.html.twig');
    }

    /**
     * @param array  $itemsMappedData
     * @param string $view
     *
     * @return array
     */
    private function getRenderedItemList(array $itemsMappedData, $view)
    {
        $renderedItems = array();
        foreach ($itemsMappedData as $item) {
            $viewRenderer = $this->getViewRenderer();
            $viewRenderer->AddSourceObjectsFromArray($item);
            $renderedItems[] = $viewRenderer->Render($view);
        }

        return $renderedItems;
    }

    /**
     * @return ViewRenderer
     */
    private function getViewRenderer()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_view_renderer.view_renderer');
    }

    /**
     * @param ResultDataInterface $results
     * @param int $currentPage
     * @param int $pageSize
     * @param string $listPagerUrl
     * @param string $moduleSpotName
     *
     * @return LegacyMockArticleListWrapper
     */
    private function getListProxy(ResultDataInterface $results, $currentPage, $pageSize, $listPagerUrl, $moduleSpotName)
    {
        return new LegacyMockArticleListWrapper($results, $currentPage, $pageSize, $listPagerUrl, $moduleSpotName);
    }
}
