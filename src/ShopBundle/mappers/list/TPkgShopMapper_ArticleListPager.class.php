<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopMapper_ArticleListPager extends AbstractViewMapper
{
    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements)
    {
        $oRequirements->NeedsSourceObject('oList', 'TdbShopArticleList');
        $oRequirements->NeedsSourceObject('listIdent', 'string');
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        /** @var $oArticleList TdbShopArticleList */
        $oArticleList = $oVisitor->GetSourceObject('oList');

        $aListPaging = array(
            'iActivePage' => $oArticleList->GetCurrentPageNumber(),
            'iLastPage' => $oArticleList->GetTotalPageCount(),
            'sURL' => str_replace(TdbShopArticleList::URL_LIST_CURRENT_PAGE.'=0', TdbShopArticleList::URL_LIST_CURRENT_PAGE.'={[pageNumber0]}', $oArticleList->GetPageJumpLink(0)),
            'sURLAjax' => str_replace(TdbShopArticleList::URL_LIST_CURRENT_PAGE.'=0', TdbShopArticleList::URL_LIST_CURRENT_PAGE.'={[pageNumber0]}', $oArticleList->GetPageJumpLinkAsAJAXCall(0)),
            'sURLAjaxPlain' => str_replace(TdbShopArticleList::URL_LIST_CURRENT_PAGE.'=0', TdbShopArticleList::URL_LIST_CURRENT_PAGE.'={[pageNumber0]}', $oArticleList->GetPageJumpLinkAsAJAXCall(0, false)),
        );

        $oVisitor->SetMappedValue('aListPaging', $aListPaging);
        $oVisitor->SetMappedValue('listIdent', $oVisitor->GetSourceObject('listIdent'));
    }
}
