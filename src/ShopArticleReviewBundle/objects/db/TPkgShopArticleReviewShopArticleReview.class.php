<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopArticleReviewShopArticleReview extends TPkgShopArticleReviewShopArticleReviewAutoParent
{
    const URL_PARAM_REVIEW_ID = 'sReviewId';

    const URL_PARAM_ACTION_ID = 'sAction';

    const URL_PARAM_REVIEW_ITEM_JUMPER = 'Review';

    /**
     * Returns URL to rate a comment.
     *
     * @param bool $bPositiveLink Set if you want the url to rate a review positive or negative
     * @param bool $bUseFullUrl
     *
     * @return string
     */
    public function GetRateURL($bPositiveLink = true, $bUseFullUrl = false)
    {
        if ($bPositiveLink) {
            $bPositiveLink = '1';
        } else {
            $bPositiveLink = '0';
        }
        $aParameter = array('bRate' => $bPositiveLink, TdbShopArticleReview::URL_PARAM_REVIEW_ID => $this->id);
        $sRatePositiveLink = TTools::GetExecuteMethodOnCurrentModuleURL('RateReview', $aParameter, $bUseFullUrl);

        return $sRatePositiveLink;
    }

    /**
     * Returns true or false if active user rated review.
     *
     * @return bool
     */
    public function ReviewRatedByActiveUser()
    {
        return array_key_exists('TPkgShopArticleReviewShopArticleReviewRated', $_SESSION) && array_key_exists($this->id, $_SESSION['TPkgShopArticleReviewShopArticleReviewRated']);
    }

    /**
     * vote the review up or down.
     *
     * @param bool $bRateUp
     *
     * @return void
     */
    public function RateReview($bRateUp = true)
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        if (false === $this->ReviewRatedByActiveUser()) {
            $sRateString = 'helpful_count';
            if (false === $bRateUp) {
                $sRateString = 'not_helpful_count';
            }

            $quotedTable = $connection->quoteIdentifier($this->table);
            $quotedId = $connection->quote($this->id);

            // Update Rating
            $query = "
            UPDATE {$quotedTable}
               SET `{$sRateString}` = `{$sRateString}` + 1
             WHERE `id` = {$quotedId}
             LIMIT 1
        ";
            $connection->executeStatement($query);

            // Fetch updated value
            $query = "
            SELECT `{$sRateString}`
              FROM {$quotedTable}
             WHERE `id` = {$quotedId}
        ";
            $statement = $connection->executeQuery($query);
            if ($aTmp = $statement->fetchAssociative()) {
                $this->sqlData[$sRateString] = $aTmp[$sRateString];

                if ($bRateUp) {
                    $this->fieldHelpfulCount = $this->sqlData[$sRateString];
                } else {
                    $this->fieldNotHelpfulCount = $this->sqlData[$sRateString];
                }
            }

            if (!array_key_exists('TPkgShopArticleReviewShopArticleReviewRated', $_SESSION)) {
                $_SESSION['TPkgShopArticleReviewShopArticleReviewRated'] = [];
            }

            $_SESSION['TPkgShopArticleReviewShopArticleReviewRated'][(string) $this->id] = time();
        }
    }

    /**
     * Returns the URL to report a review.
     *
     * @param bool $bUseFullUrl
     *
     * @return string
     */
    public function GetReportURL($bUseFullUrl = false)
    {
        $aParameter = array(TdbShopArticleReview::URL_PARAM_REVIEW_ID => $this->id);
        $sReportLink = TTools::GetExecuteMethodOnCurrentModuleURL('ReportReview', $aParameter, $bUseFullUrl);

        return $sReportLink;
    }

    /**
     * Returns URL to change report notification state for a review.
     *
     * @param bool $bUseFullUrl
     *
     * @return string
     */
    public function GetChangeReviewReportNotificationStateURL($bUseFullUrl = false)
    {
        $aParameter = array(TdbShopArticleReview::URL_PARAM_REVIEW_ID => $this->id);
        $sReportLink = TTools::GetExecuteMethodOnCurrentModuleURL('ChangeReviewReportNotificationState', $aParameter, $bUseFullUrl);

        return $sReportLink;
    }

    /**
     * Returns the URL to delete a review.
     *
     * @param bool $bUseFullUrl
     *
     * @return string
     */
    public function GetDeleteURL($bUseFullUrl = false)
    {
        $aParameter = array(TdbShopArticleReview::URL_PARAM_REVIEW_ID => $this->id);
        $sReportLink = TTools::GetExecuteMethodOnCurrentModuleURL('DeleteReview', $aParameter, $bUseFullUrl);

        return $sReportLink;
    }

    /**
     * send a review notification to the shop owner.
     *
     * @return void
     */
    public function SendReviewReportNotification()
    {
        $this->SaveActionIdToComment();
        $oMail = TDataMailProfile::GetProfile('report-review');
        $aData = array();
        $oShop = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveShop();
        $oArticle = $this->GetFieldShopArticle();
        $aData['sArticleName'] = $oArticle->GetName();
        $aData['sReviewId'] = $this->id;
        $aData['sReviewTitle'] = $this->fieldTitle;
        $aData['sReviewText'] = $this->fieldComment;
        $aData['sReviewAuthor'] = $this->fieldAuthorName;
        $aData['sUnlockReviewLink'] = "<a href='".$this->GetUnlockURL(true)."'>".TGlobal::OutHtml(\ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_article_review.action.publish_comment')).'</a> ';
        $aData['sDeleteReviewLink'] = "<a href='".$this->GetDeleteWithActionIdURL(true)."'>".TGlobal::OutHtml(\ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_article_review.action.delete_comment')).'</a> ';
        $aData['shopname'] = $oShop->GetName();
        $oMail->AddDataArray($aData);
        $oMail->SendUsingObjectView('emails', 'Customer');
    }

    /**
     * Send comment notification to review owner.
     * Send comment notification to owner only if owner set the option for the review.
     *
     * @param TdbPkgComment $oComment
     *
     * @return void
     */
    public function SendReviewCommentNotification($oComment)
    {
        if ($this->AllowSendAuthorReviewCommentNotification()) {
            $sAuthorEmail = $this->GetSendReviewCommentNotificationEmail();
            if (TTools::IsValidEMail($sAuthorEmail)) {
                $oMail = TDataMailProfile::GetProfile('review-comment');
                $aData = array();
                $oArticle = $this->GetFieldShopArticle();
                $aData['sArticleName'] = $oArticle->GetName();
                $aData['sReviewTitle'] = $this->fieldTitle;
                $aData['sReviewText'] = $this->fieldComment;
                $aData['sCommentText'] = $oComment->fieldComment;
                $oMail->AddDataArray($aData);
                $oMail->ChangeToAddress($this->fieldAuthorEmail, $this->fieldAuthorName);
                $oMail->SendUsingObjectView('emails', 'Customer');
            }
        }
    }

    /**
     * Get owner email for comment notification.
     *
     * @return string|null
     */
    protected function GetSendReviewCommentNotificationEmail()
    {
        $sSendReviewCommentNotificationEmail = '';
        if (!empty($this->fieldAuthorEmail)) {
            $sSendReviewCommentNotificationEmail = $this->fieldAuthorEmail;
        } elseif (!empty($this->fieldDataExtranetUserId)) {
            $oAuthor = TdbDataExtranetUser::GetNewInstance();
            if ($oAuthor->Load($this->fieldDataExtranetUserId)) {
                $sSendReviewCommentNotificationEmail = $oAuthor->GetUserEMail();
            }
        }

        return $sSendReviewCommentNotificationEmail;
    }

    /**
     * Checks if its allowed to send comment notification to owner.
     *
     * @return bool
     */
    protected function AllowSendAuthorReviewCommentNotification()
    {
        $bAllowSendAuthorCommentNotification = false;
        if ($this->fieldSendCommentNotification) {
            if (!empty($this->fieldAuthorEmail) || !empty($this->fieldDataExtranetUserId)) {
                $bAllowSendAuthorCommentNotification = true;
            }
        }

        return $bAllowSendAuthorCommentNotification;
    }

    /**
     * Add new unique action id to the comment.
     * Action id was needed to run an action like unlock or delete via post.
     *
     * @return void
     */
    protected function SaveActionIdToComment()
    {
        $sActionId = TTools::GetUUID();
        $this->sqlData['action_id'] = $sActionId;
        $this->AllowEditByAll(true);
        $this->Save();
        $this->AllowEditByAll(false);
    }

    /**
     * Get URL to delete review with unique action id.
     *
     * @param bool $bUseFullUrl
     *
     * @return string
     */
    protected function GetDeleteWithActionIdURL($bUseFullUrl = false)
    {
        $sDeleteURL = '';
        if (!empty($this->sqlData['action_id'])) {
            $aParameter = array(TdbShopArticleReview::URL_PARAM_ACTION_ID => $this->sqlData['action_id']);
            $sDeleteURL = TTools::GetExecuteMethodOnCurrentModuleURL('DeleteReview', $aParameter, $bUseFullUrl);
        }

        return $sDeleteURL;
    }

    /**
     * Get URL to unlock a review with unique action id.
     *
     * @param bool $bUseFullUrl
     *
     * @return string
     */
    protected function GetUnlockURL($bUseFullUrl = false)
    {
        $sUnlockURL = '';
        if (!empty($this->sqlData['action_id'])) {
            $aParameter = array(TdbShopArticleReview::URL_PARAM_ACTION_ID => $this->sqlData['action_id']);
            $sUnlockURL = TTools::GetExecuteMethodOnCurrentModuleURL('UnlockReview', $aParameter, $bUseFullUrl);
        }

        return $sUnlockURL;
    }

    /**
     * Get cache trigger for comments.
     *
     * @param string $id
     * @param array $aCallTimeVars
     *
     * @return array
     */
    protected function GetCacheTrigger($id, $aCallTimeVars = array())
    {
        $aCacheTrigger = parent::GetCacheTrigger($id, $aCallTimeVars);
        if (array_key_exists('oPkgCommentModuleConfig', $aCallTimeVars) && !is_null($aCallTimeVars['oPkgCommentModuleConfig'])) {
            $aCallTimeVars['oPkgCommentModuleConfig']->SetActiveItem($this);
            $aCacheTrigger = array_merge($aCacheTrigger, $aCallTimeVars['oPkgCommentModuleConfig']->GetCacheTrigger());
        }

        return $aCacheTrigger;
    }
}
