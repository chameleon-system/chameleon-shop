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
     style="top:<?php echo TGlobal::OutHTML($oMarker->fieldTop); ?>px;left:<?php echo TGlobal::OutHTML($oMarker->fieldLeft); ?>px">
    <div class="standard">
        <?php
        if (!is_null($oImage)) {
            ?>
            <a href="<?php echo TGlobal::OutHTML($sLink); ?>" title="<?php echo TGlobal::OutHTML($oMarker->fieldName); ?>">
                <img src="<?php echo TGlobal::OutHTML($oImage->GetFullURL()); ?>"
                    <?php if (!is_null($oHoverImage)) {
                        ?>
                     onmouseover="this.src='<?php echo TGlobal::OutHTML($oHoverImage->GetFullURL()); ?>'"
                     onmouseout="this.src='<?php echo TGlobal::OutHTML($oImage->GetFullURL()); ?>'"
                    <?php
                    } ?>            alt="<?php echo TGlobal::OutHTML($oMarker->fieldName); ?>" border="0"/>
            </a>
            <?php
        } else {
            echo 'no image defined'.$sLink;
        }
?>
    </div>
</div>