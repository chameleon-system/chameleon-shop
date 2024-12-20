<?php
$oMsgManager = TCMSMessageManager::GetInstance();
$oLocal = TCMSLocal::GetActive();
$iCount = 0;
if ($oActiveArticle) {
    ?>
<div class="moduleheader">
    <a name="<?php echo MTPkgShopArticleReviewCore::URL_PARAM_REVIEW_JUMPER; ?>"></a>
    <?php echo TGlobal::OutHTML($oModuleConfiguration->fieldTitle); ?>
    <?php
    $sIntroText = $oModuleConfiguration->GetTextField('intro_text');
    if (!empty($sIntroText)) {
        echo $sIntroText;
    } ?>
    <span class="reviewnumber">(<?php echo $oActiveArticle->GetReviewCount(); ?>)</span>
    <span class="reviewstars"><?php echo $oLocal->FormatNumber($oActiveArticle->GetReviewAverageScore(), 1); ?></span>
</div>
<div class="modulecontent">
    <?php
    if ($oMsgManager->ConsumerHasMessages(MTPkgShopArticleReview::MSG_CONSUMER_NAME)) {
        echo $oMsgManager->RenderMessages(MTPkgShopArticleReview::MSG_CONSUMER_NAME);
    } ?>
    <?php if ($bAllowReadReview) {
        ?>
    <div class="reviewlist">
        <?php
        if ($oReviewList->Length() > 0) {
            $sReviewHTML = '';
            while ($oReview = $oReviewList->Next()) {
                if ($oMsgManager->ConsumerHasMessages(TdbPkgComment::MESSAGE_CONSUMER_NAME.$oReview->id)) {
                    echo $oMsgManager->RenderMessages(TdbPkgComment::MESSAGE_CONSUMER_NAME.$oReview->id);
                }
                if ($iCount >= $iShowReviewsOnStart) {
                    $sReviewHTML .= '<div class="jshide">';
                }
                $sReviewHTML .= $oReview->Render('extended_full', 'Customer', ['bAllowRateReviews' => $bAllowRateReviews, 'bAllowReportReviews' => $bAllowReportReviews, 'oPkgCommentModuleConfig' => $oPkgCommentModuleConfig]);
                if ($iCount >= $iShowReviewsOnStart) {
                    $sReviewHTML .= '</div>';
                }
                ++$iCount;
            }
            echo $sReviewHTML;
            if ($iCount > $iShowReviewsOnStart) {
                ?>
                <script type="text/javascript">
                    document.write("<" + 'a href="" class="showall" onclick="$(\'.reviewlist .jshide\').toggle(); $(this).toggle();$(\'.showstart\').toggle();return false;"><?php echo TGlobal::OutHtml(TGlobal::Translate('chameleon_system_shop_article_review.action.show_all_reviews')); ?></a' + ">");
                    document.write("<" + 'a href="" class="showstart" onclick="$(\'.reviewlist .showall\').toggle();$(this).toggle();$(\'.reviewlist .jshide\').toggle();return false;"><?php echo TGlobal::OutHtml(TGlobal::Translate('chameleon_system_shop_article_review.action.show_fewer_reviews')); ?></a' + ">");
                    $(document).ready(function () {
                        $('.reviewlist .jshide').hide();
                        $('.reviewlist .showstart').hide()
                    });
                </script>
                <?php
            }
        } else {
            echo '<div class="no-reviews">'.TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop_article_review.text.no_reviews')).'</div>';
        } ?>
    </div>
    <?php
    } else {
        ?>
    <div
        class="please-login-message"><?php echo TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop_article_review.text.login_required_to_read_reviews')); ?></div>
    <?php
    } ?>
    <?php if ($bAllowWriteReview) {
        ?>
    <a name="<?php echo MTPkgShopArticleReviewCore::URL_PARAM_REVIEW_WRITE_JUMPER; ?>"></a>
    <div class="reviewForm">
        <div class="writeheader"><?php echo TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop_article_review.action.write_review')); ?></div>
        <form name="writereview<?php echo TGlobal::OutHTML($oActiveArticle->sqlData['cmsident']); ?>" accept-charset="utf-8"
              method="post" action="<?php echo $oActiveArticle->getLink(false, null,
                  [TdbShopArticle::CMS_LINKABLE_OBJECT_PARAM_CATEGORY => $data['oActiveCategory']->id]); ?>">
            <input type="hidden" name="module_fnc[<?php echo TGlobal::OutHTML($data['sModuleSpotName']); ?>]"
                   value="WriteReview"/>
            <?php
                  $oReviewEntryItem = TdbShopArticleReview::GetNewInstance();
        echo $oReviewEntryItem->Render('extended_form', 'Customer', ['sCaptchaQuestion' => $sCaptchaQuestion, 'aUserData' => $aUserData, 'bNeedUserFieldForName' => $bNeedUserFieldForName, 'iRatingStars' => $iRatingStars]); ?>
            <input type="submit" value="write"/>

            <div class="cleadiv">&nbsp;</div>
        </form>
    </div>
    <?php
    } else {
        ?>
    <div
        class="please-login-message"><?php echo TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop_article_review.text.login_required_to_write_review')); ?></div>
    <?php
    } ?>
    <?php
    $sOutroText = $oModuleConfiguration->GetTextField('outro_text');
    if (!empty($sOutroText)) {
        echo $sOutroText;
    } ?>
</div>
<?php
} ?>