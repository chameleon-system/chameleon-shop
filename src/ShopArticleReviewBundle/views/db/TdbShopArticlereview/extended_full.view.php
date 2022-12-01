<?php
/*@var $oReview TdbShopArticleReview*/
/*@var $aCallTimeVars array */
/*@var $oPkgCommentModuleConfig TdbPkgCommentModuleConfig */
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
    <a name="<?=TdbShopArticleReview::URL_PARAM_REVIEW_ITEM_JUMPER; ?><?=TGlobal::OutHTML($oReview->sqlData['id']); ?>"></a>

    <div class="reviewdate"><?=TGlobal::OutHTML($oLocal->FormatDate(substr($oReview->fieldDatecreated, 0, 10))); ?></div>
    <div class="reviewauthor"><?=TGlobal::OutHTML($oReview->fieldAuthorName); ?></div>
    <div class="title"><?=TGlobal::OutHTML($oReview->fieldTitle); ?></div>
    <div class="rating"><?=TGlobal::OutHTML(str_pad('', $oReview->fieldRating, '*')); ?></div>
    <div class="text"><?=TGlobal::OutHTML($oReview->fieldComment); ?></div>
    <?php if ($bAllowReportReviews) {
    ?>
    <div class="reportreview"><a
        href="<?=$oReview->GetReportURL(); ?>"><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop_article_review.action.report_as_inappropriate')); ?></a>
    </div>
    <?php
} ?>
    <div class="ownreview"><a href=""><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop_article_review.action.write_review')); ?></a></div>
    <?php if ($bAllowRateReviews) {
        ?>
    <?php if ($oReview->fieldHelpfulCount > 0) {
            ?>
        <div class="rateoverview">
            <?=TGlobal::OutHtml(TGlobal::Translate('chameleon_system_shop_article_review.text.how_helpful',
                array(
                    '%helpful%' => $oReview->fieldHelpfulCount,
                    '%sumRanked%' => $oReview->fieldHelpfulCount + $oReview->fieldNotHelpfulCount,
                )
            )); ?>
        </div>
        <?php
        } ?>
    <div class="ratereview">
        <div class="pro_rate"><a
            href="<?=$oReview->GetRateURL(true); ?>"><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop_article_review.action.vote_up')); ?></a>
        </div>
        <div class="contra_rate"><a
            href="<?=$oReview->GetRateURL(false); ?>"><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop_article_review.action.vote_down')); ?></a>
        </div>
    </div>
    <?php
    }?>
    <?php if (!is_null($oPkgCommentModuleConfig)) {
        $oUser = TdbDataExtranetUser::GetInstance(); ?>
    <?php if ((!$oPkgCommentModuleConfig->fieldGuestCanSeeComments && $oUser->IsLoggedIn()) || $oPkgCommentModuleConfig->fieldGuestCanSeeComments) {
            ?>
        <?= $oPkgCommentModuleConfig->Render('standard', array('oActiveCommentItem' => $oReview)); ?>
        <?php
        } else {
            ?>
        <div
            class="mustlogin"><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop_article_review.text.login_required')); ?></div>
        <?php
        } ?>
    <?php
    if ((!$oPkgCommentModuleConfig->fieldGuestCommentAllowed && $oUser->IsLoggedIn()) || $oPkgCommentModuleConfig->fieldGuestCommentAllowed) {
        ?>
        <a href=""
           onclick="$('.reviecommentform-<?=TGlobal::OutHTML($oReview->id); ?>').show();return false"><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop_article_review.action.comment_on_review')); ?></a>
        <div class="reviecommentform-<?=TGlobal::OutHTML($oReview->id); ?> hide">
            <div class="writecommentforreview"><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop_article_review.text.comment_on_review_headline')); ?></div>
            <form name="reguser" accept-charset="utf-8" method="post" action="" enctype="multipart/form-data">
                <input type="hidden" name="objectid" value="<?=TGlobal::OutHTML($oReview->id); ?>"/>
                <input type="hidden" name="commenttypeid"
                       value="<?=TGlobal::OutHTML($oPkgCommentModuleConfig->fieldPkgCommentTypeId); ?>"/>
                <input type="hidden" name="module_fnc[<?=TGlobal::OutHTML($sSpotName); ?>]" value="WriteComment"/>
                <textarea class="commenttextarea" cols="0" rows="0" name="commentsavetext"></textarea>
                <input type="submit" name="savecomment" class="button_savecomment"
                       value="<?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop_article_review.action.submit_comment')); ?>"/>
            </form>
        </div>
        <?php
    } else {
        ?>
        <div
            class="mustlogin"><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop_article_review.text.login_required')); ?></div>
        <?php
    } ?>
    <?php
    } ?>
    <hr>
</div>