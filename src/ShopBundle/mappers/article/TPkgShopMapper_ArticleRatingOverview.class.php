<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopMapper_ArticleRatingOverview extends AbstractPkgShopMapper_Article
{
    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager): void
    {
        /** @var $oArticle TdbShopArticle */
        $oArticle = $oVisitor->GetSourceObject('oObject');
        if ($oArticle && $bCachingEnabled) {
            $oCacheTriggerManager->addTrigger($oArticle->table, $oArticle->id);
        }
        /** @var $oReviewModuleConfiguration TdbPkgShopArticleReviewModuleShopArticleReviewConfiguration */
        $oReviewModuleConfiguration = $oVisitor->GetSourceObject('oReviewModuleConfiguration');
        $oVisitor->SetMappedValue('aRatingOverview', $this->GetRatingOverview($oReviewModuleConfiguration, $oArticle, $oCacheTriggerManager, $bCachingEnabled));
    }

    /**
     * Get array for each rating count holding the count of rated reviews.
     *
     * @param TdbPkgShopArticleReviewModuleShopArticleReviewConfiguration $oReviewModuleConfiguration
     * @param TdbShopArticle $oArticle
     * @param bool $bCachingEnabled
     *
     * @return array
     */
    protected function GetRatingOverview($oReviewModuleConfiguration, $oArticle, IMapperCacheTriggerRestricted $oCacheTriggerManager, $bCachingEnabled)
    {
        $aRatingOverview = [];
        for ($i = $oReviewModuleConfiguration->fieldRatingCount; $i > 0; --$i) {
            $aRatingOverview['r_'.$i] = [];
            $aRatingOverview['r_'.$i]['dRating'] = $i;
            $aRatingOverview['r_'.$i]['iRatingCount'] = 0;
        }
        $oReviewList = $oArticle->GetReviewsPublished();
        while ($oReview = $oReviewList->Next()) {
            if ($bCachingEnabled) {
                $oCacheTriggerManager->addTrigger($oReview->table, $oReview->id);
            }
            if (isset($aRatingOverview['r_'.$oReview->fieldRating])) {
                $aRatingOverview['r_'.$oReview->fieldRating];
                ++$aRatingOverview['r_'.$oReview->fieldRating]['iRatingCount'];
            }
        }

        return $aRatingOverview;
    }

    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements): void
    {
        parent::GetRequirements($oRequirements);
        $oRequirements->NeedsSourceObject('oReviewModuleConfiguration', 'TdbPkgShopArticleReviewModuleShopArticleReviewConfiguration');
    }
}
