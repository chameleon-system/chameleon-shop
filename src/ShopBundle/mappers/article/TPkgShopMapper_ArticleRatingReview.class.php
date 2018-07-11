<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopMapper_ArticleRatingReview extends AbstractPkgShopMapper_Article
{
    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        /** @var $oArticleReview TdbShopArticleReview */
        $oArticleReview = $oVisitor->GetSourceObject('oArticleReview');
        if ($oArticleReview && $bCachingEnabled) {
            $oCacheTriggerManager->addTrigger($oArticleReview->table, $oArticleReview->id);
        }
        /** @var $oLocal TdbCmsLocals */
        $oLocal = $oVisitor->GetSourceObject('oLocal');
        if ($oLocal && $bCachingEnabled) {
            $oCacheTriggerManager->addTrigger($oLocal->table, $oLocal->id);
        }
        $oVisitor->SetMappedValue('sTitle', $oArticleReview->fieldTitle);
        $oVisitor->SetMappedValue('sUser', $oArticleReview->fieldAuthorName);
        $oVisitor->SetMappedValue('sDate', $oLocal->FormatDate($oArticleReview->fieldDatecreated));
        $oVisitor->SetMappedValue('sText', $oArticleReview->fieldComment);
        $oVisitor->SetMappedValue('dRating', $oArticleReview->fieldRating);
    }

    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements)
    {
        parent::GetRequirements($oRequirements);
        $oRequirements->NeedsSourceObject('oArticleReview', 'TdbShopArticleReview');
        $oRequirements->NeedsSourceObject('oReviewModuleConfiguration', 'TdbPkgShopArticleReviewModuleShopArticleReviewConfiguration');
        $oRequirements->NeedsSourceObject('oLocal', 'TdbCmsLocals', TdbCmsLocals::GetActive());
    }
}
