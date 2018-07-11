<?php
/**
 * used to display a preview image for an article.
 */
/* @var $oArticle TdbShopArticle // the calling article */
/* @var $oArticleSize TdbShopArticleImageSize // the image size to use */
/* @var $oImage TCMSImage // the tcms image to show */
/* @var $oImageThumbnail TCMSImage // the tcms image to show cut to the right size based on the oArticleSize object*/
/* @var $oPreviewImage TdbShopArticlePreviewImage - the preview image object */

    if (is_a($oArticle, 'TdbShopArticle') && is_a($oImageThumbnail, 'TCMSImage')) {
        ?><img src="<?=$oImageThumbnail->GetFullURL(); ?>" alt="<?=TGlobal::OutHTML($oArticle->fieldName); ?>" border="0" /><?php
    } else {
        ?><img src="/chameleon/mediapool/error.jpg" alt="" border="0" /><?php
    }
