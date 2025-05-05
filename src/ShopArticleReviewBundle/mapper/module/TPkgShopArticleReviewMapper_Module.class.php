<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopArticleReviewMapper_Module extends AbstractViewMapper
{
    /**
     * To map values from models to views the mapper has to implement iVisitable.
     * The ViewRender will pass a prepared MapperVisitor instance to the mapper.
     *
     * The mapper has to fill the values it is responsible for in the visitor.
     *
     * example:
     *
     * $foo = $oVisitor->GetSourceObject("foomodel")->GetFoo();
     * $oVisitor->SetMapperValue("foo", $foo);
     *
     *
     * To be able to access the desired source object in the visitor, the mapper has
     * to declare this requirement in its GetRequirements method (see IViewMapper)
     *
     * @param bool $bCachingEnabled - if set to true, you need to define your cache trigger that invalidate the view rendered via mapper. if set to false, you should NOT set any trigger
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager): void
    {
        /** @var $oReviewModuleConfiguration TdbPkgShopArticleReviewModuleShopArticleReviewConfiguration */
        $oReviewModuleConfiguration = $oVisitor->GetSourceObject('oReviewModuleConfiguration');
        if ($oReviewModuleConfiguration && $bCachingEnabled) {
            $oCacheTriggerManager->addTrigger($oReviewModuleConfiguration->table, $oReviewModuleConfiguration->id);
        }
        $bAllowWriteReview = $oVisitor->GetSourceObject('bAllowWriteReview');
        $oMsgManager = TCMSMessageManager::GetInstance();
        if ($oMsgManager->ConsumerHasMessages(MTPkgShopArticleReview::MSG_CONSUMER_NAME)) {
            $sOverallMessage = $oMsgManager->RenderMessages(MTPkgShopArticleReview::MSG_CONSUMER_NAME);
            $oVisitor->SetMappedValue('sOverallMessage', $sOverallMessage);
        }
        $bFieldErrors = $oVisitor->GetSourceObject('bFieldErrors');
        if ($bFieldErrors) {
            $oVisitor->SetMappedValue('sWriteReviewHideOnJS', '');
        } else {
            $oVisitor->SetMappedValue('sWriteReviewHideOnJS', $oVisitor->GetSourceObject('sWriteReviewHideOnJS'));
        }
        $oVisitor->SetMappedValue('iItemCountStart', $oReviewModuleConfiguration->fieldCountShowReviews);
        $oVisitor->SetMappedValue('sShowAllReviews', $oVisitor->GetSourceObject('sShowAllReviews'));
        $oVisitor->SetMappedValue('sLoginHtml', $oVisitor->GetSourceObject('sLoginHtml'));
        $oVisitor->SetMappedValue('sWriteReviewHtml', $oVisitor->GetSourceObject('sWriteReviewHtml'));
        $oVisitor->SetMappedValue('bDisplayWriteReview', $bAllowWriteReview);
        $oVisitor->SetMappedValue('sLoginHideOnJS', $oVisitor->GetSourceObject('sLoginHideOnJS'));
    }

    /**
     * A mapper has to specify its requirements by providing th passed MapperRequirements instance with the
     * needed information and returning it.
     *
     * example:
     *
     * $oRequirements->NeedsSourceObject("foo",'stdClass','default-value');
     * $oRequirements->NeedsSourceObject("bar");
     * $oRequirements->NeedsMappedValue("baz");
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements): void
    {
        $oRequirements->NeedsSourceObject('oReviewModuleConfiguration', 'TdbPkgShopArticleReviewModuleShopArticleReviewConfiguration');
        $oRequirements->NeedsSourceObject('sShowAllReviews', '');
        $oRequirements->NeedsSourceObject('bAllowWriteReview', '');
        $oRequirements->NeedsSourceObject('sLoginHtml', '');
        $oRequirements->NeedsSourceObject('sWriteReviewHtml', null, '');
        $oRequirements->NeedsSourceObject('sWriteReviewHideOnJS', null, '');
        $oRequirements->NeedsSourceObject('bFieldErrors');
        $oRequirements->NeedsSourceObject('sLoginHideOnJS', null, '');
    }
}
