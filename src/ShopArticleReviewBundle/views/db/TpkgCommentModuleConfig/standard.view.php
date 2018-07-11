<?php
$oUser = TdbDataExtranetUser::GetInstance();
$iCommentNr = $iCommentNr - ($iAktPage - 1) * $iPageSizege;
$iCount = 0;
$iShowCommentsOnStart = $oModconf->fieldCountShowReviews;
?>
<div class="TPkgCommentModuleconfig">
    <div class="standard">
        <?php if ($oCommentList->Length() > 0) {
    ?>
        <hr>
        <div class="commentliststart"><?=TGlobal::OutHtml(TGlobal::Translate('chameleon_system_shop_article_review.text.review_comments')); ?></div>
        <div class="commentlist">
            <?php
            $oCommentList->GoToStart();
    while ($oComment = &$oCommentList->Next()) { /*@var $oComment TdbPkgComment*/
        if ($iCount >= $iShowCommentsOnStart) {
            echo '<div class="jshide">';
        }
        echo $oComment->Render('standard', array('iCommentNr' => $iCommentNr, 'oActiveItem' => $oActiveItem, 'iAktPage' => $iAktPage, 'sAnnounceCommentLink' => $sAnnounceCommentLink, 'bAllowReportComment' => $oModconf->fieldAllowReportComments));
        if ($iCount >= $iShowCommentsOnStart) {
            echo '</div>';
        }
        ++$iCount;
        --$iCommentNr;
    }
    if ($iCount > $iShowCommentsOnStart) {
        ?>
                <script type="text/javascript">
                    document.write("<" + 'a href="" class="comment_showall" onclick="$(\'.commentlist .jshide\').toggle(); $(this).toggle();$(\'.comment_showstart\').toggle();return false;"><?=TGlobal::OutHtml(TGlobal::Translate('chameleon_system_shop_article_review.action.show_all_comments')); ?></a' + ">");
                    document.write("<" + 'a href="" class="comment_showstart" onclick="$(\'.commentlist .comment_showall\').toggle();$(this).toggle();$(\'.commentlist .jshide\').toggle();return false;"><?=TGlobal::OutHtml(TGlobal::Translate('chameleon_system_shop_article_review.action.show_fewer_comments')); ?></a' + ">");
                    $(document).ready(function () {
                        $('.commentlist .jshide').hide();
                        $('.commentlist .comment_showstart').hide()
                    });
                </script>
                <?php
    } ?>
        </div>
        <?php
} ?>
    </div>
</div>
