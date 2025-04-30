<?php
/**
 * used to display a preview image for an article.
 */
/* @var $oArticle TdbShopArticle // the calling article */
/* @var $oArticleSize TdbShopArticleImageSize // the image size to use */
/* @var $oImage TCMSImage // the tcms image to show */
/* @var $oImageThumbnail TCMSImage // the tcms image to show cut to the right size based on the oArticleSize object */
/* @var $oPreviewImage TdbShopArticlePreviewImage - the preview image object */
?>
<?php echo $oImage->renderImage($oImageSize->fieldWidth, $oImageSize->fieldHeight, 800, 600); ?>