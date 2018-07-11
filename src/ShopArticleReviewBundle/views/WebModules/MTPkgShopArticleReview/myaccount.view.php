<?php
$oMsgManager = TCMSMessageManager::GetInstance();
$oLocal = TCMSLocal::GetActive();
$iCount = 0;
?>
<div class="moduleheader">
    <a name="<?=MTPkgShopArticleReviewCore::URL_PARAM_REVIEW_JUMPER; ?>"></a>
    <?=TGlobal::OutHTML($oModuleConfiguration->fieldTitle); ?>
    <?php
    $sIntroText = $oModuleConfiguration->GetTextField('intro_text');
    if (!empty($sIntroText)) {
        echo $sIntroText;
    }
    ?>
    <span class="reviewnumber"><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop_article_review.text.review_count', array('%count%' => $oReviewList->Length()))); ?></span>
</div>
<div class="modulecontent">
    <?php
    if ($oMsgManager->ConsumerHasMessages(MTPkgShopArticleReview::MSG_CONSUMER_NAME)) {
        echo $oMsgManager->RenderMessages(MTPkgShopArticleReview::MSG_CONSUMER_NAME);
    }
    ?>
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
                $sReviewHTML .= $oReview->Render('extended_owner', 'Customer', array('bAllowRateReviews' => $bAllowRateReviews, 'bAllowReportReviews' => $bAllowReportReviews, 'oPkgCommentModuleConfig' => $oPkgCommentModuleConfig, 'iRatingStars' => $iRatingStars));
                if ($iCount >= $iShowReviewsOnStart) {
                    $sReviewHTML .= '</div>';
                }
                ++$iCount;
            }
            echo $sReviewHTML;
            if ($iCount > $iShowReviewsOnStart) {
                ?>
                <script type="text/javascript">
                    document.write("<" + 'a href="" class="showall" onclick="$(\'.reviewlist .jshide\').toggle(); $(this).toggle();$(\'.showstart\').toggle();return false;"><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop_article_review.action.show_all_reviews')); ?></a' + ">");
                    document.write("<" + 'a href="" class="showstart" onclick="$(\'.reviewlist .showall\').toggle();$(this).toggle();$(\'.reviewlist .jshide\').toggle();return false;"><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop_article_review.action.show_fewer_reviews')); ?></a' + ">");
                    $(document).ready(function () {
                        $('.reviewlist .jshide').hide();
                        $('.reviewlist .showstart').hide()
                    });
                </script>
                <?php
            }
        } else {
            echo '<div class="no-reviews">'.TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop_article_review.text.you_have_no_reviews')).'</div>';
        } ?>
    </div>
    <?php
    } else {
        ?>
    <div
        class="please-login-message"><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop_article_review.text.login_required_to_read_reviews')); ?></div>
    <?php
    } ?>
    <?php
    $sOutroText = $oModuleConfiguration->GetTextField('outro_text');
    if (!empty($sOutroText)) {
        echo $sOutroText;
    }
    ?>
</div>