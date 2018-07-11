<?php
/** @var $oMarker TdbPkgImageHotspotItemMarker */
$oImage = $oMarker->GetImage(0, 'cms_media_id');
$oHoverImage = $oMarker->GetImage(0, 'cms_media_hover_id');
$sLink = $oMarker->GetURLForConnectedRecord();
if (empty($sLink)) {
    $sLink = $oMarker->fieldUrl;
}
?>
<div class="TPkgImageHotspotItemMarker"
     style="top:<?=TGlobal::OutHTML($oMarker->fieldTop); ?>px;left:<?=TGlobal::OutHTML($oMarker->fieldLeft); ?>px">
    <div class="standard">
        <?php
        if (!is_null($oImage)) {
            if (!$oImage->IsFlashMovie()) {
                ?>
                <a href="<?=TGlobal::OutHTML($sLink); ?>" title="<?=TGlobal::OutHTML($oMarker->fieldName); ?>">
                    <img src="<?=TGlobal::OutHTML($oImage->GetFullURL()); ?>"
                        <?php         if (!is_null($oHoverImage)) {
                    ?>
                         onmouseover="this.src='<?=TGlobal::OutHTML($oHoverImage->GetFullURL()); ?>'"
                         onmouseout="this.src='<?=TGlobal::OutHTML($oImage->GetFullURL()); ?>'"
                        <?php
                } ?>            alt="<?=TGlobal::OutHTML($oMarker->fieldName); ?>" border="0"/>
                </a>
                <?php
            } else {
                echo $oImage->GetThumbnailTag(300, 300, null, null);
            }
        } else {
            echo 'no image defined'.$sLink;
        }
        ?>
    </div>
</div>