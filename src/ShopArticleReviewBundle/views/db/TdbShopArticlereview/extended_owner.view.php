<?php
/*@var $oReview TdbShopArticleReview */
/*@var $aCallTimeVars array */
$oGlobal = TGlobal::instance();
$oMsgManager = TCMSMessageManager::GetInstance();
$oLocal = TCMSLocal::GetActive();
$oModulePointer = $oGlobal->GetExecutingModulePointer();
$sSpotName = $oModulePointer->sModuleSpotName;
$oArticle = $oReview->GetFieldShopArticle();
$bAllowRateReviews = false;
$bAllowReportReviews = false;
$oPkgCommentModuleConfig = null;
$iRatingStars = 5;
if (array_key_exists('bAllowRateReviews', $aCallTimeVars)) {
    $bAllowRateReviews = $aCallTimeVars['bAllowRateReviews'];
}
if (array_key_exists('bAllowReportReviews', $aCallTimeVars)) {
    $bAllowReportReviews = $aCallTimeVars['bAllowReportReviews'];
}
if (array_key_exists('oPkgCommentModuleConfig', $aCallTimeVars)) {
    $oPkgCommentModuleConfig = $aCallTimeVars['oPkgCommentModuleConfig'];
}
if (array_key_exists('iRatingStars', $aCallTimeVars)) {
    $iRatingStars = $aCallTimeVars['iRatingStars'];
}
?>
<div class="TShopArticleReview">
    <div class="owner">
        <div class="reviewitem">
            <?php
            $sArticleName = \ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_article_review.text.unknown_product');
            if (!is_null($oArticle)) {
                $sArticleName = '<a href="'.$oArticle->GetDetailLink().'">'.TGlobal::OutHTML($oArticle->GetName()).'</a>';
            }
            $status = \ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_article_review.text.waiting_for_publication');
            if ($oReview->fieldPublish) {
                $status = \ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_article_review.text.published');
            }
            ?>
            <a name="<?=TdbShopArticleReview::URL_PARAM_REVIEW_ITEM_JUMPER; ?><?=TGlobal::OutHTML($oReview->sqlData['id']); ?>"></a>

            <div class="state"><?=TGlobal::OutHTML($status); ?></div>
            <div
                class="reviewdate"><?=TGlobal::OutHTML($oLocal->FormatDate(substr($oReview->fieldDatecreated, 0, 10))); ?></div>
            <div class="reviewauthor"><?=TGlobal::OutHTML($oReview->fieldAuthorName); ?></div>
            <div class="title"><?=TGlobal::OutHTML($oReview->fieldTitle); ?></div>
            <div class="rating"><?=TGlobal::OutHTML(str_pad('', $oReview->fieldRating, '*')); ?></div>
            <div class="text"><?=TGlobal::OutHTML($oReview->fieldComment); ?></div>
            <?php if ($oReview->fieldHelpfulCount > 0) {
                ?>
            <div class="rateoverview">
                <?=TGlobal::OutHTML(\ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_article_review.text.how_helpful', array(
                    '%helpful%' => $oReview->fieldHelpfulCount,
                    '%sumRanked%' => $oReview->fieldHelpfulCount + $oReview->fieldNotHelpfulCount,
                ))); ?>
            </div>
            <?php
            }?>
            <div class="notificationinfo">
                <?php
                echo TGlobal::OutHTML(\ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_article_review.form.send_notifications'));
                if ($oReview->fieldSendCommentNotification) {
                    echo TGlobal::OutHTML(\ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_article_review.text.comment_notification_active'));
                    echo '<a href="'.$oReview->GetChangeReviewReportNotificationStateURL().'">'.TGlobal::OutHTML(\ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_article_review.action.disable_comment_notification')).'</a>';
                } else {
                    echo TGlobal::OutHTML(\ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_article_review.text.comment_notification_inactive'));
                    echo '<a href="'.$oReview->GetChangeReviewReportNotificationStateURL().'">'.TGlobal::OutHTML(\ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_article_review.action.enable_comment_notification')).'</a>';
                }
                ?>
            </div>
            <div class="delete"><a
                href="<?=$oReview->GetDeleteURL(); ?>"><?=TGlobal::OutHTML(\ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_article_review.action.delete_review')); ?></a></div>
            <form name="writereview<?=TGlobal::OutHTML($oReview->sqlData['cmsident']); ?>" accept-charset="utf-8"
                  method="post" action="">
                <input type="hidden" name="module_fnc[<?=TGlobal::OutHTML($sSpotName); ?>]" value="EditReview"/>
                <input type="hidden" name="<?=TGlobal::OutHTML(TdbShopArticleReview::URL_PARAM_REVIEW_ID); ?>"
                       value="<?=TGlobal::OutHTML($oReview->id); ?>"/>
                <table class="standardtable">
                    <tr>
                        <th><?=TGlobal::OutHtml(\ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_article_review.form.title')); ?></th>
                        <td><input type="text" class="userinput"
                                   name="<?=TGlobal::OutHTML(TdbShopArticleReview::INPUT_BASE_NAME).'[title]'; ?>"
                                   value="<?=TGlobal::OutHTML($oReview->fieldTitle); ?>"/></td>
                    </tr>
                    <tr>
                        <th><?=TGlobal::OutHTML(\ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_article_review.form.rating')).': *'; ?></th>
                        <td>
                            <?php
                            echo'<div class="starsContainer">';
                            for ($iRating = $iRatingStars; $iRating > 0; --$iRating) {
                                echo '<div>';
                                $sChecked = '';
                                if ($iRating == $oReview->fieldRating) {
                                    $sChecked = 'checked ="checked"';
                                }
                                echo '<label><input class="reviewRadioButton plain" type="radio" name="'.TGlobal::OutHTML(TdbShopArticleReview::INPUT_BASE_NAME)."[rating]\" value=\"{$iRating}\" ".$sChecked.' />';
                                for ($iTmp = 0; $iTmp < $iRating; ++$iTmp) {
                                    echo '<img src="/static/images/star.png" alt="'.TGlobal::OutHTML(\ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_article_review.form.rating_star')).'" border="0" />';
                                }
                                echo '</label>';
                                echo '</div>';
                            }
                            echo'</div>';
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="comment"><?=TGlobal::OutHTML(\ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_article_review.form.comment')); ?>: *</th>
                        <td>
                            <?php
                            $error_style = '';
                            if ($oMsgManager->ConsumerHasMessages(TdbShopArticleReview::MSG_CONSUMER_BASE_NAME.'-comment')) {
                                echo $oMsgManager->RenderMessages(TdbShopArticleReview::MSG_CONSUMER_BASE_NAME.'-comment');
                                $error_style = ' style="border:1px solid red"';
                            }
                            ?>
                            <textarea class="userinput"<?=$error_style; ?> rows="5" cols="40"
                                      name="<?=TGlobal::OutHTML(TdbShopArticleReview::INPUT_BASE_NAME); ?>[comment]"><?=TGlobal::OutHTML($oReview->fieldComment); ?></textarea>
                        </td>
                    </tr>
                </table>
                <input type="submit" value="edit"/>
            </form>
            <hr>
            <?php if (!is_null($oPkgCommentModuleConfig)) {
                                $oUser = TdbDataExtranetUser::GetInstance(); ?>
            <?php if ((!$oPkgCommentModuleConfig->fieldGuestCanSeeComments && $oUser->IsLoggedIn()) || $oPkgCommentModuleConfig->fieldGuestCanSeeComments) {
                                    ?>
                <?= $oPkgCommentModuleConfig->Render('standard', array('oActiveCommentItem' => $oReview)); ?>
                <?php
                                } else {
                                    ?>
                <div
                    class="mustlogin"><?=TGlobal::OutHtml(\ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_article_review.text.login_required')); ?></div>
                <?php
                                } ?>
            <?php
                            } ?>
        </div>
    </div>
</div>
