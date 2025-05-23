<?php
/** @var $oSpot TdbPkgImageHotspotItemSpot */
/** @var $oItem Tdb* */
$oItem = $oSpot->GetSpotObject();
$sLink = $oSpot->GetURLForConnectedRecord();
$iMarkerWidth = 21;
$iMarkerHeight = 21;
?>
<div class="TPkgImageHotspotItemSpot"
     style="top:<?php echo TGlobal::OutHTML($oSpot->fieldTop - round($iMarkerHeight / 2)); ?>px;left:<?php echo TGlobal::OutHTML($oSpot->fieldLeft - round($iMarkerWidth / 2)); ?>px">
    <div class="standard">
        <div class="spotmarker" onclick="document.location.href='<?php echo TGlobal::OutHTML($sLink); ?>';">
            <a href="<?php echo TGlobal::OutHTML($sLink); ?>">
                <img src="<?php echo URL_USER_CMS_PUBLIC; ?>/blackbox/pkgImageHotspot/marker.png" alt="" border="0"/>
            </a>

            <div class="objectDisplayBlock">
                <img src="<?php echo URL_USER_CMS_PUBLIC; ?>/blackbox/pkgImageHotspot/nose.png" alt="" class="blockNose"/>

                <div class="objectDisplayBlockContent">
                    <?php if (method_exists($oItem, 'Render')) {
                        echo $oItem->Render('hotspot', 'Customer');
                    } ?>
                </div>
            </div>
        </div>
    </div>
</div>