<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopArticleReviewMapper_Write extends AbstractViewMapper
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
     * @param \IMapperVisitorRestricted     $oVisitor
     * @param bool                          $bCachingEnabled      - if set to true, you need to define your cache trigger that invalidate the view rendered via mapper. if set to false, you should NOT set any trigger
     * @param IMapperCacheTriggerRestricted $oCacheTriggerManager
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        $aUserReviewData = $oVisitor->GetSourceObject('aUserReviewData');
        /** @var $oUser TdbDataExtranetUser */
        $oUser = $oVisitor->GetSourceObject('oUser');
        $aWriteReviewData = array();
        $aFieldUserName = array();
        $aFieldUserName['sError'] = $this->GetMessageForField('author_name');
        $aFieldUserName['sValue'] = $this->GetValueForField('author_name', $aUserReviewData);
        $oVisitor->SetMappedValue('aFieldUserName', $aFieldUserName);

        $aFieldTitle = array();
        $aFieldTitle['sError'] = $this->GetMessageForField('title');
        $aFieldTitle['sValue'] = $this->GetValueForField('title', $aUserReviewData);
        $oVisitor->SetMappedValue('aFieldTitle', $aFieldTitle);

        $aFieldRating = array();
        $aFieldRating['sError'] = $this->GetMessageForField('rating');
        $aFieldRating['sValue'] = $this->GetValueForField('rating', $aUserReviewData);
        $oVisitor->SetMappedValue('aFieldRating', $aFieldRating);

        $aFieldText = array();
        $aFieldText['sError'] = $this->GetMessageForField('comment');
        $aFieldText['sValue'] = $this->GetValueForField('comment', $aUserReviewData);
        $oVisitor->SetMappedValue('aFieldText', $aFieldText);

        $sSpotName = $oVisitor->GetSourceObject('sSpotName');
        $oVisitor->SetMappedValue('sSpotName', $sSpotName);
        $sUserId = $oUser->id;
        $oVisitor->SetMappedValue('sUserId', $sUserId);
        $sUserEmail = $oUser->fieldEmail;
        $oVisitor->SetMappedValue('sUserEmail', $sUserEmail);
    }

    /**
     * @param string $sFieldName
     *
     * @return string
     */
    protected function GetMessageForField($sFieldName)
    {
        $sMessage = '';
        $oMsgManager = TCMSMessageManager::GetInstance();
        if ($oMsgManager->ConsumerHasMessages(TdbShopArticleReview::MSG_CONSUMER_BASE_NAME.'-'.$sFieldName)) {
            $sMessage = $oMsgManager->RenderMessages(TdbShopArticleReview::MSG_CONSUMER_BASE_NAME.'-'.$sFieldName);
        }

        return $sMessage;
    }

    /**
     * @template T
     * @param string $sFieldName
     * @param array<string, T> $aUserData
     * @return T|'' - empty string if the field does not exist
     */
    protected function GetValueForField($sFieldName, $aUserData)
    {
        $sValue = '';
        if (isset($aUserData[$sFieldName])) {
            $sValue = $aUserData[$sFieldName];
        }

        return $sValue;
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
     *
     * @param IMapperRequirementsRestricted $oRequirements
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements)
    {
        $oRequirements->NeedsSourceObject('sSpotName');
        $oRequirements->NeedsSourceObject('aUserReviewData', 'array');
        $oRequirements->NeedsSourceObject('oUser', 'TdbDataExtranetUser');
    }
}
