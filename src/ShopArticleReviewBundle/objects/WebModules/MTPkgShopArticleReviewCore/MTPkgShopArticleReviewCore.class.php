<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\ActivePageServiceInterface;
use ChameleonSystem\ShopArticleReviewBundle\AuthorDisplayConstants;

/**
 * used to show and write article reviews.
/**/
class MTPkgShopArticleReviewCore extends TUserCustomModelBase
{
    /** @var TdbPkgShopArticleReviewModuleShopArticleReviewConfiguration */
    protected $oModuleConfiguration = null;

    /** @var string|false */
    protected $sPkgCommentTypeId = false;

    const MSG_CONSUMER_NAME = 'MTPkgShopArticleReview';

    const SESSION_CAPTCHA = 'MTPkgShopArticleReviewCaptcha';

    const URL_PARAM_REVIEW_JUMPER = 'ReviewStart';

    const URL_PARAM_REVIEW_WRITE_JUMPER = 'WriteReview';

    /**
     * called before any external functions get called, but after the constructor.
     */
    public function Init()
    {
        parent::Init();
        $this->GetModuleConfiguration();
        $this->data['aUserData'] = $this->SetDefaultFieldVars();
    }

    /**
     * Set default form parameter with init value or post values.
     *
     * @return array
     */
    protected function SetDefaultFieldVars()
    {
        if ($this->global->UserDataExists(TdbShopArticleReview::INPUT_BASE_NAME)) {
            $aPostUserData = $this->global->GetuserData(TdbShopArticleReview::INPUT_BASE_NAME);
        }
        $aDefaultFieldDataList = array('comment' => '', 'author_email' => '', 'author_name' => '', 'title' => '', 'send_comment_notification' => '0', 'rating' => 1);
        if (isset($aPostUserData) && is_array($aPostUserData)) {
            foreach ($aDefaultFieldDataList as $sDefaultFieldDataKey => $aDefaultFieldDataValue) {
                if (!array_key_exists($sDefaultFieldDataKey, $aPostUserData)) {
                    $aPostUserData[$sDefaultFieldDataKey] = $aDefaultFieldDataValue;
                }
            }
            $aDefaultFieldDataList = $aPostUserData;
        }

        return $aDefaultFieldDataList;
    }

    public function Execute()
    {
        $this->data = parent::Execute();
        $this->data['oActiveArticle'] = null;
        $this->data['oActiveCategory'] = null;
        $this->data['oReviewList'] = $this->GetReviews();
        $this->data['sCaptchaQuestion'] = $this->GenerateCaptcha();
        $oModuleConfiguration = $this->GetModuleConfiguration();
        $this->data['oModuleConfiguration'] = $oModuleConfiguration;
        $this->data['bAllowWriteReview'] = $this->AllowWriteReview();
        $this->data['bAllowReadReview'] = $this->AllowReadReview();
        $this->data['bNeedUserFieldForName'] = $this->NeedUserFieldForName();
        $this->data['iRatingStars'] = $oModuleConfiguration->fieldRatingCount;
        $this->data['bAllowRateReviews'] = $this->AllowRateReviews();
        $this->data['bAllowReportReviews'] = $this->AllowReportReviews();
        $this->data['oPkgCommentModuleConfig'] = $this->GetPkgCommentModuleConfiguration();
        $this->data['iShowReviewsOnStart'] = $oModuleConfiguration->fieldCountShowReviews;

        return $this->data;
    }

    /**
     * Returns virtual comment module config. Was needed to comment reviews
     * note: this function will be used only if package pkg comment was installed.
     *
     * @return TdbPkgCommentModuleConfig|null
     */
    protected function GetPkgCommentModuleConfiguration()
    {
        $oPkgCommentModuleConfig = null;
        $oModuleConfiguration = $this->GetModuleConfiguration();
        if ($this->AllowToCommentReview()) {
            $oPkgCommentModuleConfig = TdbPkgCommentModuleConfig::GetNewInstance();
            $oPkgCommentModuleConfig->fieldGuestCanSeeComments = !$oModuleConfiguration->fieldAllowShowReviewLoggedinUsersOnly;
            $oPkgCommentModuleConfig->fieldNewestOnTop = true;
            $oPkgCommentModuleConfig->fieldNumberOfCommentsPerPage = 0;
            $oPkgCommentModuleConfig->fieldGuestCommentAllowed = !$oModuleConfiguration->fieldAllowWriteReviewLoggedinUsersOnly;
            $oPkgCommentModuleConfig->fieldPkgCommentTypeId = $this->GetCommentTypeId();

            /**
             * @psalm-suppress InvalidPropertyAssignmentValue
             * @FIXME field is `bool` but assigned `int` - This could yield unwanted behaviour, especially with strict checks
             */
            $oPkgCommentModuleConfig->fieldUseSimpleReporting = 1;

            /**
             * @psalm-suppress InvalidPropertyAssignmentValue
             * @FIXME field is `bool` but assigned `int` - This could yield unwanted behaviour, especially with strict checks
             */
            $oPkgCommentModuleConfig->fieldShowReportedComments = 0;

            /**
             * @psalm-suppress UndefinedPropertyAssignment
             * @FIXME Does `fieldCountShowReviews` exist?
             */
            $oPkgCommentModuleConfig->fieldCountShowReviews = $oModuleConfiguration->fieldCountShowReviews;

            /**
             * @psalm-suppress UndefinedPropertyAssignment
             * @FIXME Does `fieldAllowReportComments` exist?
             */
            $oPkgCommentModuleConfig->fieldAllowReportComments = $this->AllowReportReviews();
        }

        return $oPkgCommentModuleConfig;
    }

    /**
     * Checks if its allowed to write a comment to a review.
     * Returns always false if package pkgcomment was not installed.
     *
     * @return bool
     */
    protected function AllowToCommentReview()
    {
        if ($this->GetCommentTypeId()) {
            $oModuleConfiguration = $this->GetModuleConfiguration();
            if ($oModuleConfiguration->fieldAllowCommentReviews) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the comment type for article reviews.
     *
     * @return string
     */
    protected function GetCommentTypeId()
    {
        if (false === $this->sPkgCommentTypeId) {
            $sQuery = "SELECT * FROM `pkg_comment_type` WHERE `pkg_comment_type`.`class_name` = 'TPkgCommentTypePkgShopArticleReview'";
            $oRes = MySqlLegacySupport::getInstance()->query($sQuery);
            if (MySqlLegacySupport::getInstance()->num_rows($oRes) > 0) {
                $aRow = MySqlLegacySupport::getInstance()->fetch_assoc($oRes);
                $this->sPkgCommentTypeId = $aRow['id'];
            } else {
                $this->sPkgCommentTypeId = '';
            }
        }

        return $this->sPkgCommentTypeId;
    }

    /**
     * Checks if its allowed to red reviews.
     *
     * @return bool
     */
    protected function AllowReadReview()
    {
        $bAllowReadReview = false;
        $oModuleConfiguration = $this->GetModuleConfiguration();
        $oUser = TdbDataExtranetUser::GetInstance();
        if ($oModuleConfiguration->fieldAllowShowReviewLoggedinUsersOnly && $oUser->IsLoggedIn() || !$oModuleConfiguration->fieldAllowShowReviewLoggedinUsersOnly) {
            $bAllowReadReview = true;
        }

        return $bAllowReadReview;
    }

    /**
     * Checks if its allowed to write reviews.
     *
     * @return bool
     */
    protected function AllowWriteReview()
    {
        $bAllowWriteReview = false;
        $oModuleConfiguration = $this->GetModuleConfiguration();
        $oUser = TdbDataExtranetUser::GetInstance();
        if ($oModuleConfiguration->fieldAllowWriteReviewLoggedinUsersOnly && $oUser->IsLoggedIn() || !$oModuleConfiguration->fieldAllowWriteReviewLoggedinUsersOnly) {
            $bAllowWriteReview = true;
        }

        return $bAllowWriteReview;
    }

    /**
     * Checks if its allowed to rate reviews.
     *
     * @return bool
     */
    protected function AllowRateReviews()
    {
        $bAllowRateReviews = $this->AllowReadReview();
        if ($bAllowRateReviews) {
            $oModuleConfiguration = $this->GetModuleConfiguration();
            if (!$oModuleConfiguration->fieldAllowRateReview) {
                $bAllowRateReviews = false;
            }
        }

        return $bAllowRateReviews;
    }

    /**
     * Checks if its allowed to report reviews.
     *
     * @return bool
     */
    protected function AllowReportReviews()
    {
        $bAllowRateReviews = $this->AllowReadReview();
        if ($bAllowRateReviews) {
            $oModuleConfiguration = $this->GetModuleConfiguration();
            if (!$oModuleConfiguration->fieldAllowReportReviews) {
                $bAllowRateReviews = false;
            }
        }

        return $bAllowRateReviews;
    }

    /**
     * Check if captcha field is needed to show in forms.
     *
     * @return bool
     */
    protected function NeedCaptcha()
    {
        $bNeedCaptcha = false;
        $oUser = TdbDataExtranetUser::GetInstance();
        $oModuleConfiguration = $this->GetModuleConfiguration();
        if (!$oModuleConfiguration->fieldAllowWriteReviewLoggedinUsersOnly && !$oUser->IsLoggedIn()) {
            $bNeedCaptcha = true;
        }

        return $bNeedCaptcha;
    }

    /**
     * Get the module config for the module instance.
     *
     * @return TdbPkgShopArticleReviewModuleShopArticleReviewConfiguration
     */
    protected function GetModuleConfiguration()
    {
        if (is_null($this->oModuleConfiguration)) {
            $oModuleConfiguration = TdbPkgShopArticleReviewModuleShopArticleReviewConfiguration::GetNewInstance();
            if ($oModuleConfiguration->LoadFromField('cms_tpl_module_instance_id', $this->instanceID)) {
            }
            $this->oModuleConfiguration = $oModuleConfiguration;
        }

        return $this->oModuleConfiguration;
    }

    /**
     * add your custom methods as array to $this->methodCallAllowed here
     * to allow them to be called from web.
     */
    protected function DefineInterface()
    {
        parent::DefineInterface();
        $this->methodCallAllowed[] = 'WriteReview';
        $this->methodCallAllowed[] = 'RateReview';
        $this->methodCallAllowed[] = 'ReportReview';
        $this->methodCallAllowed[] = 'DeleteReview';
        $this->methodCallAllowed[] = 'UnlockReview';
        $this->methodCallAllowed[] = 'EditReview';
        $this->methodCallAllowed[] = 'ChangeReviewReportNotificationState';
        // pkgComment functions
        $this->methodCallAllowed[] = 'WriteComment';
        $this->methodCallAllowed[] = 'ReportComment';
        $this->methodCallAllowed[] = 'RespondToComment';
        $this->methodCallAllowed[] = 'EditComment';
        $this->methodCallAllowed[] = 'DeleteComment';
    }

    /**
     * {@inheritdoc}
     */
    public function _CallMethod($sMethodName, $aMethodParameter = array())
    {
        $functionResult = null;
        if (true === \method_exists($this, $sMethodName)) {
            $functionResult = parent::_CallMethod($sMethodName);
        } else {
            if ($this->AllowToCommentReview()) {
                $oModule = TTools::GetModuleObject('MTPkgComment', 'standard', array(), $this->sModuleSpotName);
                $oModuleCommentConfiguration = $this->GetPkgCommentModuleConfiguration();
                $oModule->SetModuleConfig($oModuleCommentConfiguration);
                if ('WriteComment' == $sMethodName) {
                    $oModule->SetSuppressRedirectAfterAction(true);
                }
                $oNewComment = $oModule->_CallMethod($sMethodName);
                if ($oNewComment && 'WriteComment' == $sMethodName) {
                    $this->PostWriteComment($oNewComment);
                    $this->RedirectToItemPage();
                }
                $functionResult = $oNewComment;
            } else {
                trigger_error('Trying to comment a review, but the review module has been configured to not allow commenting reviews.', E_USER_WARNING);
            }
        }

        return $functionResult;
    }

    /**
     * Was called after a new comment was written.
     * note: this function will be used only if package pkg comment was installed.
     *
     * @param TdbPkgComment $oNewComment
     *
     * @return void
     */
    protected function PostWriteComment($oNewComment)
    {
        $oGlobal = TGlobal::instance();
        $oCommentReview = TdbShopArticleReview::GetNewInstance();
        if ($oCommentReview->Load($oGlobal->GetUserData('objectid'))) {
            $this->getCacheService()->callTrigger($oCommentReview->table, $oCommentReview->id);
            $oCommentReview->SendReviewCommentNotification($oNewComment);
        }
    }

    /**
     * return true if the method is white-listed for access without Authenticity token. Note: you will still need
     * to define the permitted methods via DefineInterface.
     *
     * @param string $sMethodName
     *
     * @return bool
     */
    public function AllowAccessWithoutAuthenticityToken($sMethodName)
    {
        $bAllow = parent::AllowAccessWithoutAuthenticityToken($sMethodName);
        if (false === $bAllow) {
            if ('DeleteReview' == $sMethodName) {
                $bAllow = true;
            } elseif ('UnlockReview' == $sMethodName) {
                $bAllow = true;
            }
        }

        return $bAllow;
    }

    /**
     * Redirect to review item page and set anchor to start of the review module.
     *
     * @param TCMSRecord $oReviewItem
     * @param array $aAddParameter
     * @param bool $bGoToWriteReviewFrom
     *
     * @return never
     */
    protected function RedirectToItemPage($oReviewItem = null, $aAddParameter = array(), $bGoToWriteReviewFrom = false)
    {
        $oActivePage = $this->getActivePageService()->getActivePage();
        $sRedirectURL = $oActivePage->GetRealURLPlain($aAddParameter);
        if (null !== $oReviewItem) {
            $sRedirectURL .= '#'.TdbShopArticleReview::URL_PARAM_REVIEW_ITEM_JUMPER.$oReviewItem->sqlData['id'];
        } else {
            if ($bGoToWriteReviewFrom) {
                $sRedirectURL .= '#'.self::URL_PARAM_REVIEW_WRITE_JUMPER;
            } else {
                $sRedirectURL .= '#'.self::URL_PARAM_REVIEW_JUMPER;
            }
        }

        $this->getRedirect()->redirect($sRedirectURL);
    }

    /**
     * Returns the reviews for active article.
     * If no active article exits then get the reviews from logged in user. (My Account page).
     *
     * @return TdbShopArticleReviewList|null
     */
    protected function GetReviews()
    {
        $oActiveArticle = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveProduct();
        $oReviewList = null;
        if ($oActiveArticle) {
            $oReviewList = $this->GetReviewsForArticle($oActiveArticle);
        } else {
            $oReviewList = $this->GetReviewsForUser();
        }

        return $oReviewList;
    }

    /**
     * get the reviews from logged in user.
     *
     * @return TdbShopArticleReviewList|null
     */
    protected function GetReviewsForUser()
    {
        $oUserReviews = null;
        $oUser = TdbDataExtranetUser::GetInstance();
        if ($oUser->IsLoggedIn()) {
            $oUserReviews = $oUser->GetFieldShopArticleReviewList();
        }

        return $oUserReviews;
    }

    /**
     * Get the reviews for given shop article.
     *
     * @param TdbShopArticle $oActiveArticle
     *
     * @return TdbShopArticleReviewList
     */
    protected function GetReviewsForArticle($oActiveArticle)
    {
        $oModuleConfiguration = $this->GetModuleConfiguration();
        if ($oActiveArticle->IsVariant()) {
            $oActiveArticle = $oActiveArticle->GetFieldVariantParent();
        }
        $oActiveCategory = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveCategory();

        $this->data['oActiveCategory'] = $oActiveCategory;
        $this->data['oActiveArticle'] = $oActiveArticle;
        if ($oModuleConfiguration->fieldAllowRateReview) {
            $oReviewList = TdbShopArticleReviewList::GetReviewsForArticleSortedByRate($oActiveArticle->id);
        } else {
            $oReviewList = TdbShopArticleReviewList::GetListForShopArticleId($oActiveArticle->id);
        }
        $oReviewList->AddFilterString("`shop_article_review`.`publish`='1'");

        return $oReviewList;
    }

    /**
     * Unlocks a locked review.
     *
     * @return void
     */
    protected function UnlockReview()
    {
        $oGlobal = TGlobal::instance();
        $oMsgManager = TCMSMessageManager::GetInstance();
        if ($oGlobal->UserDataExists(TdbShopArticleReview::URL_PARAM_ACTION_ID)) {
            $oReviewItem = TdbShopArticleReview::GetNewInstance();
            if ($oReviewItem->LoadFromField('action_id', $oGlobal->GetUserData(TdbShopArticleReview::URL_PARAM_ACTION_ID))) {
                if (0 == $oReviewItem->sqlData['publish']) {
                    $oReviewItem->AllowEditByAll(true);
                    $oReviewItem->sqlData['publish'] = true;
                    $oReviewItem->sqlData['action_id'] = '';
                    $oReviewItem->Save();
                    $oArticle = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveProduct();
                    if ($oArticle->IsVariant()) {
                        $oArticle = $oArticle->GetFieldVariantParent();
                    }
                    $oArticle->UpdateStatsReviews();
                    $oMsgManager->AddMessage(self::MSG_CONSUMER_NAME, 'ARTICLE-REVIEW-UNLOCK-SUCCESS');
                } else {
                    $oMsgManager->AddMessage(self::MSG_CONSUMER_NAME, 'ARTICLE-REVIEW-ACTION-REVIEW-NOT-BLOCKED');
                }
            } else {
                $oMsgManager->AddMessage(self::MSG_CONSUMER_NAME, 'ARTICLE-REVIEW-ACTION-REVIEW-NOT-FOUND');
            }
        } else {
            $oMsgManager->AddMessage(self::MSG_CONSUMER_NAME, 'ARTICLE-REVIEW-ACTION-MISSING-PARAMETER');
        }
        $this->RedirectToItemPage();
    }

    /**
     * Changes the report notification state for one review.
     * this can only be called from the owning user.
     *
     * @return void
     */
    public function ChangeReviewReportNotificationState()
    {
        $oGlobal = TGlobal::instance();
        $oMsgManager = TCMSMessageManager::GetInstance();
        if ($oGlobal->UserDataExists(TdbShopArticleReview::URL_PARAM_REVIEW_ID)) {
            $oReviewItem = TdbShopArticleReview::GetNewInstance();
            if ($oReviewItem->Load($oGlobal->GetUserData(TdbShopArticleReview::URL_PARAM_REVIEW_ID)) && $oReviewItem->IsOwner()) {
                $sReviewReportNotificationState = $oReviewItem->fieldSendCommentNotification;
                if ($sReviewReportNotificationState) {
                    $sReviewReportNotificationState = 0;
                } else {
                    $sReviewReportNotificationState = 1;
                }
                $oReviewItem->sqlData['send_comment_notification'] = $sReviewReportNotificationState;
                $oReviewItem->AllowEditByAll(true);
                $oReviewItem->Save();
                $oMsgManager->AddMessage(self::MSG_CONSUMER_NAME, 'ARTICLE-REVIEW-CHANGE-REPORT-NOTIFICATION-STATE-SUCCESS');
            } else {
                $oMsgManager->AddMessage(self::MSG_CONSUMER_NAME, 'ARTICLE-REVIEW-ACTION-REVIEW-NOT-FOUND');
            }
        } else {
            $oMsgManager->AddMessage(self::MSG_CONSUMER_NAME, 'ARTICLE-REVIEW-ACTION-MISSING-PARAMETER');
        }
        $this->RedirectToItemPage();
    }

    /**
     * Deletes one review.
     * This function can only be called with valid action id or from owning user.
     *
     * @return void
     */
    public function DeleteReview()
    {
        $oGlobal = TGlobal::instance();
        $oMsgManager = TCMSMessageManager::GetInstance();
        if ($oGlobal->UserDataExists(TdbShopArticleReview::URL_PARAM_ACTION_ID)) {
            $this->DeleteReviewFromActionId();
        } elseif ($oGlobal->UserDataExists(TdbShopArticleReview::URL_PARAM_REVIEW_ID)) {
            $this->DeleteReviewFromOwner();
        } else {
            $oMsgManager->AddMessage(self::MSG_CONSUMER_NAME, 'ARTICLE-REVIEW-ACTION-MISSING-PARAMETER');
        }
        $this->RedirectToItemPage();
    }

    /**
     * Deletes one review with valid action id.
     *
     * @return void
     */
    protected function DeleteReviewFromActionId()
    {
        $oReviewItem = TdbShopArticleReview::GetNewInstance();
        $oGlobal = TGlobal::instance();
        $oMsgManager = TCMSMessageManager::GetInstance();
        if ($oReviewItem->LoadFromField('action_id', $oGlobal->GetUserData(TdbShopArticleReview::URL_PARAM_ACTION_ID))) {
            if (0 == $oReviewItem->sqlData['publish']) {
                $this->DeleteConnectedComments($oReviewItem);
                $oReviewItem->AllowEditByAll(true);
                $oReviewItem->Delete();
                $oMsgManager->AddMessage(self::MSG_CONSUMER_NAME, 'ARTICLE-REVIEW-DELETE-SUCCESS');
            } else {
                $oMsgManager->AddMessage(self::MSG_CONSUMER_NAME, 'ARTICLE-REVIEW-ACTION-REVIEW-NOT-BLOCKED');
            }
        } else {
            $oMsgManager->AddMessage(self::MSG_CONSUMER_NAME, 'ARTICLE-REVIEW-ACTION-REVIEW-NOT-FOUND');
        }
    }

    /**
     * Deletes one review if owner is logged in.
     *
     * @return void
     */
    protected function DeleteReviewFromOwner()
    {
        $oReviewItem = TdbShopArticleReview::GetNewInstance();
        $oGlobal = TGlobal::instance();
        $oMsgManager = TCMSMessageManager::GetInstance();
        if ($oReviewItem->Load($oGlobal->GetUserData(TdbShopArticleReview::URL_PARAM_REVIEW_ID)) && $oReviewItem->IsOwner()) {
            $this->DeleteConnectedComments($oReviewItem);
            $oReviewItem->AllowEditByAll(true);
            $oReviewItem->Delete();
            $oMsgManager->AddMessage(self::MSG_CONSUMER_NAME, 'ARTICLE-REVIEW-DELETE-SUCCESS');
        } else {
            $oMsgManager->AddMessage(self::MSG_CONSUMER_NAME, 'ARTICLE-REVIEW-ACTION-REVIEW-NOT-FOUND');
        }
    }

    /**
     * Deletes all comment for a given review.
     *
     * @param TdbShopArticleReview $oReviewItem
     *
     * @return void
     */
    protected function DeleteConnectedComments($oReviewItem)
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        if ($this->AllowToCommentReview()) {
            $quotedTableName = $connection->quote($oReviewItem->table);
            $quotedItemId = $connection->quote($oReviewItem->id);

            $sQuery = "
            SELECT `pkg_comment`.*
              FROM `cms_tbl_conf`
        INNER JOIN `pkg_comment_type` ON `pkg_comment_type`.`cms_tbl_conf_id` = `cms_tbl_conf`.`id`
        INNER JOIN `pkg_comment` ON `pkg_comment`.`pkg_comment_type_id` = `pkg_comment_type`.`id`
             WHERE `pkg_comment_type`.`class_name` = 'TPkgCommentTypePkgShopArticleReview'
               AND `cms_tbl_conf`.`name` = {$quotedTableName}
               AND `pkg_comment`.`item_id` = {$quotedItemId}
        ";

            $oConnectedCommentList = TdbPkgCommentList::GetList($sQuery);

            while ($oConnectedComment = $oConnectedCommentList->Next()) {
                $oCommentEditor = TTools::GetTableEditorManager('pkg_comment', $oConnectedComment->id);
                $oCommentEditor->AllowDeleteByAll(true);
                $oCommentEditor->Delete($oConnectedComment->id);
            }
        }
    }

    /**
     * Reports one review to shop owner and lock reported review.
     * Shop owner owner will get an email with delete and unlock link.
     *
     * @return void
     */
    public function ReportReview()
    {
        $oGlobal = TGlobal::instance();
        $oMsgManager = TCMSMessageManager::GetInstance();
        if ($oGlobal->UserDataExists(TdbShopArticleReview::URL_PARAM_REVIEW_ID) && $this->AllowReportReviews()) {
            $oReviewItem = TdbShopArticleReview::GetNewInstance();
            if ($oReviewItem->Load($oGlobal->GetUserData(TdbShopArticleReview::URL_PARAM_REVIEW_ID))) {
                if (0 != $oReviewItem->sqlData['publish'] && '0' != $oReviewItem->sqlData['publish']) {
                    $oReviewItem->sqlData['publish'] = 0;
                    $oReviewItem->AllowEditByAll(true);
                    $oReviewItem->Save();
                    $oReviewItem->SendReviewReportNotification();
                    $oArticle = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveProduct();
                    if ($oArticle->IsVariant()) {
                        $oArticle = $oArticle->GetFieldVariantParent();
                    }
                    $oArticle->UpdateStatsReviews();
                    $oMsgManager->AddMessage(self::MSG_CONSUMER_NAME, 'ARTICLE-REVIEW-REPORT-REVIEW-SUCCESS');
                    $this->getRedirect()->redirect($this->getActivePageService()->getActivePage()->GetRealURLPlain());
                } else {
                    $oMsgManager->AddMessage(self::MSG_CONSUMER_NAME, 'ARTICLE-REVIEW-REPORT-REVIEW-ALREADY-BLOCKED');
                }
            } else {
                $oMsgManager->AddMessage(self::MSG_CONSUMER_NAME, 'ARTICLE-REVIEW-ACTION-REVIEW-NOT-FOUND');
            }
        } else {
            $oMsgManager->AddMessage(self::MSG_CONSUMER_NAME, 'ARTICLE-REVIEW-ACTION-MISSING-PARAMETER');
        }
        $this->RedirectToItemPage();
    }

    /**
     * Rates one review positive or negative.
     *
     * @return void
     */
    public function RateReview()
    {
        $oGlobal = TGlobal::instance();
        $oMsgManager = TCMSMessageManager::GetInstance();
        $oReviewItem = TdbShopArticleReview::GetNewInstance();
        if ($oReviewItem->Load($oGlobal->GetUserData(TdbShopArticleReview::URL_PARAM_REVIEW_ID))) {
            if ($this->AllowRateReviews() && $oGlobal->UserDataExists('bRate') && $oGlobal->UserDataExists(TdbShopArticleReview::URL_PARAM_REVIEW_ID)) {
                $bRate = $oGlobal->GetUserData('bRate');
                if ($bRate) {
                    $oReviewItem->RateReview(true);
                } else {
                    $oReviewItem->RateReview(false);
                }
                $oMsgManager->AddMessage(self::MSG_CONSUMER_NAME, 'ARTICLE-REVIEW-RATE-REVIEW-SUCCESS');
            } else {
                $oMsgManager->AddMessage(self::MSG_CONSUMER_NAME, 'ARTICLE-REVIEW-ACTION-MISSING-PARAMETER');
            }
        } else {
            $oMsgManager->AddMessage(self::MSG_CONSUMER_NAME, 'ARTICLE-REVIEW-ACTION-REVIEW-NOT-FOUND');
        }
        $this->RedirectToItemPage();
    }

    /**
     * Edit one review.
     *
     * @return void
     */
    public function EditReview()
    {
        $oGlobal = TGlobal::instance();
        $oMsgManager = TCMSMessageManager::GetInstance();
        if ($oGlobal->UserDataExists(TdbShopArticleReview::URL_PARAM_REVIEW_ID) && $oGlobal->UserDataExists(TdbShopArticleReview::INPUT_BASE_NAME)) {
            $oEditReview = TdbShopArticleReview::GetNewInstance();
            if ($oEditReview->Load($oGlobal->GetUserData(TdbShopArticleReview::URL_PARAM_REVIEW_ID))) {
                $aUserData = $oGlobal->GetuserData(TdbShopArticleReview::INPUT_BASE_NAME);
                foreach ($aUserData as $sKey => $sValue) {
                    $oEditReview->sqlData[$sKey] = $sValue;
                }
                if ($this->ValidateWriteReviewData($oEditReview->sqlData)) {
                    $oEditReview->AllowEditByAll(true);
                    $oEditReview->Save();
                    $oMsgManager->AddMessage(self::MSG_CONSUMER_NAME, 'ARTICLE-REVIEW-EDIT-REVIEW-SUCCESS');
                }
            } else {
                $oMsgManager->AddMessage(self::MSG_CONSUMER_NAME, 'ARTICLE-REVIEW-ACTION-REVIEW-NOT-FOUND');
            }
        } else {
            $oMsgManager->AddMessage(self::MSG_CONSUMER_NAME, 'ARTICLE-REVIEW-ACTION-MISSING-PARAMETER');
        }
    }

    /**
     * Validates data to edit a review.
     *
     * @param array $aUserData
     *
     * @return bool
     */
    protected function ValidateEditReviewData($aUserData)
    {
        $bDataValid = false;
        $oMsgManager = TCMSMessageManager::GetInstance();
        if (is_array($aUserData)) {
            $bDataValid = true;
            $sCaptcha = '';
            if ($this->NeedCaptcha()) {
                if (array_key_exists('captcha', $aUserData)) {
                    $sCaptcha = trim($aUserData['captcha']);
                }
                if (empty($sCaptcha) || $sCaptcha != $this->GetCaptchaValue()) {
                    $bDataValid = false;
                    $oMsgManager->AddMessage(TdbShopArticleReview::MSG_CONSUMER_BASE_NAME.'-captcha', 'INPUT-ERROR-INVALID-CAPTCHA');
                }
            }
            $aRequiredFields = $this->GetRequiredFields();
            foreach ($aRequiredFields as $sFieldName) {
                $sVal = '';
                if (array_key_exists($sFieldName, $aUserData)) {
                    $sVal = trim($aUserData[$sFieldName]);
                }
                if (empty($sVal)) {
                    $bDataValid = false;
                    $oMsgManager->AddMessage(TdbShopArticleReview::MSG_CONSUMER_BASE_NAME.'-'.$sFieldName, 'ERROR-USER-REQUIRED-FIELD-MISSING');
                }
            }
        }

        return $bDataValid;
    }

    /**
     * Writes a review.
     *
     * @return void
     */
    public function WriteReview()
    {
        //validate user input...
        $oGlobal = TGlobal::instance();
        $aUserData = array();
        if ($this->AllowWriteReview()) {
            $aUserData = $this->GetReviewWriteData();
            $oGlobal->GetuserData(TdbShopArticleReview::INPUT_BASE_NAME);
            if ($this->ValidateWriteReviewData($aUserData)) {
                $oArticle = $this->GetArticleToReview();
                $oReviewItem = $this->CreateReview($aUserData, $oArticle);
                $oArticle->UpdateStatsReviews();
                $oReviewItem->SendNewReviewNotification();
                $oMsgManager = TCMSMessageManager::GetInstance();
                $oMsgManager->AddMessage(self::MSG_CONSUMER_NAME, 'ARTICLE-REVIEW-SUBMITTED', $aUserData);
                $this->RedirectToItemPage();
            }
        }
        $this->RedirectToItemPage(null, array(TdbShopArticleReview::INPUT_BASE_NAME => $aUserData), true);
    }

    /**
     * @return TdbShopArticle|null
     */
    protected function GetArticleToReview()
    {
        $oArticle = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveProduct();
        if ($oArticle->IsVariant()) {
            $oArticle = $oArticle->GetFieldVariantParent();
        }

        return $oArticle;
    }

    /**
     * @return array<string, mixed>
     */
    protected function GetReviewWriteData()
    {
        $oGlobal = TGlobal::instance();

        /** @var array<string, mixed> $aUserData */
        $aUserData = $oGlobal->GetuserData(TdbShopArticleReview::INPUT_BASE_NAME);
        $aUserData['author_name'] = $this->GetAuthorName($aUserData['author_name']);

        // force user email if user is logged in
        $oUser = TdbDataExtranetUser::GetInstance();
        if ($oUser->IsLoggedIn()) {
            $aUserData['author_email'] = $oUser->GetUserEMail();
            $aUserData['data_extranet_user_id'] = $oUser->id;
        }

        return $aUserData;
    }

    /**
     * Create a new review for given article with given data.
     *
     * @param array          $aUserData
     * @param TdbShopArticle $oArticle
     *
     * @return TdbShopArticleReview
     */
    protected function CreateReview($aUserData, $oArticle)
    {
        $oModuleConfiguration = $this->GetModuleConfiguration();
        $oReviewItem = TdbShopArticleReview::GetNewInstance(); /*@var $oReviewItem TdbShopArticleReview*/
        $aUserData['shop_article_id'] = $oArticle->id;
        $oReviewItem->LoadFromRowProtected($aUserData);
        if ($oModuleConfiguration->fieldManageReviews) {
            $oReviewItem->sqlData['publish'] = '0';
        } else {
            $oReviewItem->sqlData['publish'] = '1';
        }
        $oReviewItem->AllowEditByAll(true);
        $oReviewItem->Save();

        return $oReviewItem;
    }

    /**
     * Returns required field for a review.
     *
     * @return array
     */
    protected function GetRequiredFields()
    {
        $oModuleConfiguration = $this->GetModuleConfiguration();
        if ($oModuleConfiguration->fieldAllowWriteReviewLoggedinUsersOnly) {
            $aRequiredFieldList = array('rating', 'author_name', 'data_extranet_user_id', 'comment');
        } else {
            $aRequiredFieldList = array('rating', 'author_name', 'comment');
        }

        return $aRequiredFieldList;
    }

    /**
     * Validates given review data to write a new review.
     *
     * @param array $aUserData
     *
     * @return bool
     */
    protected function ValidateWriteReviewData($aUserData)
    {
        $bDataValid = false;
        $oMsgManager = TCMSMessageManager::GetInstance();
        if (is_array($aUserData)) {
            $bDataValid = true;
            $sCaptcha = '';
            if ($this->NeedCaptcha()) {
                if (array_key_exists('captcha', $aUserData)) {
                    $sCaptcha = trim($aUserData['captcha']);
                }
                if (empty($sCaptcha) || $sCaptcha != $this->GetCaptchaValue()) {
                    $bDataValid = false;
                    $oMsgManager->AddMessage(TdbShopArticleReview::MSG_CONSUMER_BASE_NAME.'-captcha', 'INPUT-ERROR-INVALID-CAPTCHA');
                }
            }
            $aRequiredFields = $this->GetRequiredFields();
            foreach ($aRequiredFields as $sFieldName) {
                $sVal = '';
                if (array_key_exists($sFieldName, $aUserData)) {
                    $sVal = trim($aUserData[$sFieldName]);
                }
                if (empty($sVal)) {
                    $bDataValid = false;
                    $oMsgManager->AddMessage(TdbShopArticleReview::MSG_CONSUMER_BASE_NAME.'-'.$sFieldName, 'ERROR-USER-REQUIRED-FIELD-MISSING');
                }
            }

            if ($this->InsertOfReviewLocked()) {
                $bDataValid = false;
                $oMsgManager->AddMessage(MTPkgShopArticleReview::MSG_CONSUMER_NAME, 'ERROR-REVIEW-IS-LOCKED');
            }
        }

        return $bDataValid;
    }

    /**
     * @return bool
     */
    protected function InsertOfReviewLocked()
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $bReviewLocked = false;

        $oArticle = $this->GetArticleToReview();

        if (
            isset($_SESSION[TdbShopArticleReview::SESSION_REVIEWED_KEY_NAME]) &&
            is_array($_SESSION[TdbShopArticleReview::SESSION_REVIEWED_KEY_NAME]) &&
            in_array($oArticle->id, $_SESSION[TdbShopArticleReview::SESSION_REVIEWED_KEY_NAME], true)
        ) {
            $bReviewLocked = true;
        }

        if (!$bReviewLocked) {
            $quotedArticleId = $connection->quote($oArticle->id);

            $sQuery = "
            SELECT COUNT(*) AS count
              FROM `shop_article_review`
             WHERE `shop_article_id` = {$quotedArticleId}
        ";

            $oUser = TdbDataExtranetUser::GetInstance();

            if ($oUser->IsLoggedIn()) {
                $quotedUserId = $connection->quote($oUser->id);
                $sQuery .= " AND `data_extranet_user_id` = {$quotedUserId}";
            } else {
                $sUserIp = TCMSSmartURLData::GetUserIp();
                if (CHAMELEON_PKG_SHOP_REVIEWS_ANONYMIZE_IP) {
                    $sUserIp = md5($sUserIp);
                }
                $quotedUserIp = $connection->quote($sUserIp);
                $quotedDateCreated = $connection->quote(date('Y-m-d H:i:s', time() - 60 * 60 * 3));

                $sQuery .= " AND `user_ip` = {$quotedUserIp} AND `datecreated` > {$quotedDateCreated}";
            }

            $statement = $connection->executeQuery($sQuery);
            $aRow = $statement->fetchAssociative();

            if (!$aRow || (int) $aRow['count'] > 0) {
                $bReviewLocked = true;
            }
        }

        return $bReviewLocked;
    }

    /**
     * Checks if user have to enter user name manually or user name comes from logged in user.
     *
     * @return bool
     */
    protected function NeedUserFieldForName()
    {
        $bNeedUserFieldForName = false;
        $oUser = TdbDataExtranetUser::GetInstance();
        $oModuleConfiguration = $this->GetModuleConfiguration();
        if ($oModuleConfiguration->fieldAllowWriteReviewLoggedinUsersOnly) {
            if (AuthorDisplayConstants::AUTHOR_DISPLAY_TYPE_ALIAS_PROVIDED == $oModuleConfiguration->fieldOptionShowAuthorName) {
                $bNeedUserFieldForName = true;
            }
        } else {
            if (AuthorDisplayConstants::AUTHOR_DISPLAY_TYPE_ALIAS_PROVIDED == $oModuleConfiguration->fieldOptionShowAuthorName || (AuthorDisplayConstants::AUTHOR_DISPLAY_TYPE_ALIAS == $oModuleConfiguration->fieldOptionShowAuthorName && !$oUser->IsLoggedIn()) || AuthorDisplayConstants::AUTHOR_DISPLAY_TYPE_FULL_NAME == $oModuleConfiguration->fieldOptionShowAuthorName && !$oUser->IsLoggedIn()) {
                $bNeedUserFieldForName = true;
            }
        }

        return $bNeedUserFieldForName;
    }

    /**
     * Gets the author name from post data or form logged in user.
     *
     * @param string|false $sUserPostName
     *
     * @return false|string
     */
    protected function GetAuthorName($sUserPostName = false)
    {
        $oModuleConfiguration = $this->GetModuleConfiguration();
        $oAuthor = TdbDataExtranetUser::GetInstance();
        if ($this->NeedUserFieldForName()) {
            $sAuthor = $sUserPostName;
        } else {
            switch ($oModuleConfiguration->fieldOptionShowAuthorName) {
                case AuthorDisplayConstants::AUTHOR_DISPLAY_TYPE_FULL_NAME:
                    $sAuthor = $oAuthor->fieldFirstname.' '.$oAuthor->fieldLastname;
                    break;
                case AuthorDisplayConstants::AUTHOR_DISPLAY_TYPE_INITIALS:
                    $sAuthor = $oAuthor->fieldFirstname.' '.substr($oAuthor->fieldLastname, 0, 1).'.';
                    break;
                case AuthorDisplayConstants::AUTHOR_DISPLAY_TYPE_ALIAS:
                    $sAuthor = $oAuthor->fieldAliasName;
                    if (empty($sAuthor)) {
                        $sAuthor = $sUserPostName;
                    }
                    if (0 !== strcmp($sUserPostName, $oAuthor->fieldAliasName) && $oAuthor->IsLoggedIn() && $oAuthor->validateUserAlias($sUserPostName)) {
                        $oAuthor->SaveFieldsFast(array('alias_name' => $sUserPostName));
                    }
                    break;
                case AuthorDisplayConstants::AUTHOR_DISPLAY_TYPE_ANONYMOUS:
                default:
                    $sAuthor = \ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_article_review.text.anonymous');
                    break;
            }
        }
        if (empty($sAuthor)) {
            $sAuthor = \ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_article_review.text.anonymous');
        }

        return $sAuthor;
    }

    /**
     * gets captcha value fro session.
     *
     * @return bool
     */
    protected function GetCaptchaValue()
    {
        $sCaptchaValue = false;
        if (array_key_exists(self::SESSION_CAPTCHA, $_SESSION)) {
            $sCaptchaValue = $_SESSION[self::SESSION_CAPTCHA];
        }

        return $sCaptchaValue;
    }

    /**
     * Generates new captcha.
     *
     * @return string|false
     */
    protected function GenerateCaptcha()
    {
        $sCaptchaQuestion = false;
        if ($this->NeedCaptcha()) {
            $num1 = rand(1, 10);
            $num2 = rand(1, 10);
            $val = $num1 + $num2;
            $_SESSION[self::SESSION_CAPTCHA] = $val;
            $sCaptchaQuestion = ('Was ergibt '.$num1.' + '.$num2.' ?');
        }

        return $sCaptchaQuestion;
    }

    /**
     * if this function returns true, then the result of the execute function will be cached
     * Set to false because module uses forms and messages.
     * Rednerd items in moduel views will be cached only.
     *
     * @return bool
     */
    public function _AllowCache()
    {
        return false;
    }

    /**
     * @return string[]
     */
    public function GetHtmlHeadIncludes()
    {
        $aIncludes = parent::GetHtmlHeadIncludes();
        $aIncludes = array_merge($aIncludes, $this->getResourcesForSnippetPackage('common/userInput/form'));
        $aIncludes = array_merge($aIncludes, $this->getResourcesForSnippetPackage('common/lists'));

        return $aIncludes;
    }

    /**
     * @return ActivePageServiceInterface
     */
    private function getActivePageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.active_page_service');
    }

    /**
     * @return ICmsCoreRedirect
     */
    private function getRedirect()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.redirect');
    }
}
