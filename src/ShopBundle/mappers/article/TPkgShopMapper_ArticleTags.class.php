<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\SystemPageServiceInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class TPkgShopMapper_ArticleTags extends AbstractPkgShopMapper_Article
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
        $oArticleTags = $oArticle->GetFieldCmsTagsList('`cms_tags`.`count` DESC, `cms_tags`.`name`');
        $aArticleTags = [];
        $systemPageService = $this->getSystemPageService();
        $searchSystemPage = $systemPageService->getSystemPage('search');
        while ($oTag = $oArticleTags->Next()) {
            $sSearchPageUrl = '';
            $aTag = [
                'iCount' => $oTag->fieldCount,
                'sName' => $oTag->fieldName,
                'sUrlName' => $oTag->fieldUrlname,
            ];
            if (null !== $searchSystemPage) {
                try {
                    $sSearchPageUrl = $systemPageService->getLinkToSystemPageRelative('search', [
                        'q' => $oTag->fieldName,
                    ]);
                } catch (RouteNotFoundException $e) {
                    $sSearchPageUrl = '';
                }
            }
            $aTag['sUrlSearch'] = $sSearchPageUrl;
            $aArticleTags[] = $aTag;
        }
        $oVisitor->SetMappedValue('aArticleTags', $aArticleTags);
    }

    /**
     * @return SystemPageServiceInterface
     */
    private function getSystemPageService()
    {
        return ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.system_page_service');
    }
}
