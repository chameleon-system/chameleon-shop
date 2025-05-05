<?php
/* @var $oReview TdbShopArticleReview */
/* @var $aCallTimeVars array */
/* @var $oPkgCommentModuleConfig TdbPkgCommentModuleConfig */
$oGlobal = TGlobal::instance();
$oLocal = TCMSLocal::GetActive();
$oModulePointer = $oGlobal->GetExecutingModulePointer();
$sSpotName = $oModulePointer->sModuleSpotName;
$bAllowRateReviews = false;
$bAllowReportReviews = false;
$oPkgCommentModuleConfig = null;
if (array_key_exists('bAllowRateReviews', $aCallTimeVars)) {
    $bAllowRateReviews = $aCallTimeVars['bAllowRateReviews'];
}
if (array_key_exists('bAllowReportReviews', $aCallTimeVars)) {
    $bAllowReportReviews = $aCallTimeVars['bAllowReportReviews'];
}
if (array_key_exists('oPkgCommentModuleConfig', $aCallTimeVars)) {
    $oPkgCommentModuleConfig = $aCallTimeVars['oPkgCommentModuleConfig'];
}
?>
<div class="reviewitem">
    <a name="<?php echo TdbShopArticleReview::URL_PARAM_REVIEW_ITEM_JUMPER; ?><?php echo TGlobal::OutHTML($oReview->sqlData['id']); ?>"></a>

    <div class="reviewdate"><?php echo TGlobal::OutHTML($oLocal->FormatDate(substr($oReview->fieldDatecreated, 0, 10))); ?></div>
    <div class="reviewauthor"><?php echo TGlobal::OutHTML($oReview->fieldAuthorName); ?></div>
    <div class="title"><?php echo TGlobal::OutHTML($oReview->fieldTitle); ?></div>
    <div class="rating"><?php echo TGlobal::OutHTML(str_pad('', $oReview->fieldRating, '*')); ?></div>
    <div class="text"><?php echo TGlobal::OutHTML($oReview->fieldComment); ?></div>
    <?php if ($bAllowReportReviews) {
        ?>
    <div class="reportreview"><a
        href="<?php echo $oReview->GetReportURL(); ?>"><?php echo TGlobal::OutHTML(ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_article_review.action.report_as_inappropriate')); ?></a>
    </div>
    <?php
    } ?>
    <div class="ownreview"><a href=""><?php echo TGlobal::OutHTML(ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_article_review.action.write_review')); ?></a></div>
    <?php if ($bAllowRateReviews) {
        ?>
    <?php if ($oReview->fieldHelpfulCount > 0) {
        ?>
        <div class="rateoverview">
            <?php echo TGlobal::OutHtml(ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_article_review.text.how_helpful',
                [
                    '%helpful%' => $oReview->fieldHelpfulCount,
                    '%sumRanked%' => $oReview->fieldHelpfulCount + $oReview->fieldNotHelpfulCount,
                ]
            )); ?>
        </div>
        <?php
    } ?>
    <div class="ratereview">
        <div class="pro_rate"><a
            href="<?php echo $oReview->GetRateURL(true); ?>"><?php echo TGlobal::OutHTML(ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_article_review.action.vote_up')); ?></a>
        </div>
        <div class="contra_rate"><a
            href="<?php echo $oReview->GetRateURL(false); ?>"><?php echo TGlobal::OutHTML(ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_article_review.action.vote_down')); ?></a>
        </div>
    </div>
    <?php
    }?>
    <?php if (!is_null($oPkgCommentModuleConfig)) {
        $oUser = TdbDataExtranetUser::GetInstance(); ?>
    <?php if ((!$oPkgCommentModuleConfig->fieldGuestCanSeeComments && $oUser->IsLoggedIn()) || $oPkgCommentModuleConfig->fieldGuestCanSeeComments) {
        ?>
        <?php echo $oPkgCommentModuleConfig->Render('standard', ['oActiveCommentItem' => $oReview]); ?>
        <?php
    } else {
        ?>
        <div
            class="mustlogin"><?php echo TGlobal::OutHTML(ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_article_review.text.login_required')); ?></div>
        <?php
    } ?>
    <?php
    if ((!$oPkgCommentModuleConfig->fieldGuestCommentAllowed && $oUser->IsLoggedIn()) || $oPkgCommentModuleConfig->fieldGuestCommentAllowed) {
        ?>
        <a href=""
           onclick="$('.reviecommentform-<?php echo TGlobal::OutHTML($oReview->id); ?>').show();return false"><?php echo TGlobal::OutHTML(ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_article_review.action.comment_on_review')); ?></a>
        <div class="reviecommentform-<?php echo TGlobal::OutHTML($oReview->id); ?> hide">
            <div class="writecommentforreview"><?php echo TGlobal::OutHTML(ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_article_review.text.comment_on_review_headline')); ?></div>
            <form name="reguser" accept-charset="utf-8" method="post" action="" enctype="multipart/form-data">
                <input type="hidden" name="objectid" value="<?php echo TGlobal::OutHTML($oReview->id); ?>"/>
                <input type="hidden" name="commenttypeid"
                       value="<?php echo TGlobal::OutHTML($oPkgCommentModuleConfig->fieldPkgCommentTypeId); ?>"/>
                <input type="hidden" name="module_fnc[<?php echo TGlobal::OutHTML($sSpotName); ?>]" value="WriteComment"/>
                <textarea class="commenttextarea" cols="0" rows="0" name="commentsavetext"></textarea>
                <input type="submit" name="savecomment" class="button_savecomment"
                       value="<?php echo TGlobal::OutHTML(ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_article_review.action.submit_comment')); ?>"/>
            </form>
        </div>
        <?php
    } else {
        ?>
        <div
            class="mustlogin"><?php echo TGlobal::OutHTML(ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_article_review.text.login_required')); ?></div>
        <?php
    } ?>
    <?php
    } ?>
    <hr>
</div>