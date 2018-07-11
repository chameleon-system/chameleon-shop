<?php
/** @var $oDisplayHandler TShopVariantDisplayHandlerSelectboxes */
/** @var $oArticle TdbShopArticle */
/** @var $oVariantSet TdbShopVariantSet */
/** @var $aSelectedTypeValues array */
?>
<div class="TdbShopVariantDisplayHandler TShopVariantDisplayHandlerSelectboxes">
    <div class="vStandard">
        <?php
        $sArticleDetailURL = $oArticle->GetDetailLink();
        if ($oArticle->IsVariant()) {
            $oParent = $oArticle->GetFieldVariantParent();
            $sArticleDetailURL = $oParent->GetDetailLink();
        }
        ?>
        <form id="sVariantSelect<?=TGlobal::OutHTML($oArticle->id); ?>" accept-charset="utf8" method="post"
              action="<?=$sArticleDetailURL; ?>">
            <?php

            $oVariantTypes = $oVariantSet->GetFieldShopVariantTypeList();
            $oArticleVariantValues = $oArticle->GetFieldShopVariantTypeValueList();

            $aTmpSelectValue = array();
            while ($oVariantType = $oVariantTypes->Next()) {
                $oAvailableValues = $oArticle->GetVariantValuesAvailableForType($oVariantType, $aTmpSelectValue);
                $sActiveValueForVariantType = '';
                if (array_key_exists($oVariantType->id, $aSelectedTypeValues)) {
                    $sActiveValueForVariantType = $aSelectedTypeValues[$oVariantType->id];
                }

                echo '<select name="'.TdbShopVariantType::URL_PARAMETER.'['.TGlobal::OutHTML($oVariantType->id).']" onchange="document.getElementById(\'sVariantSelect'.TGlobal::OutHTML($oArticle->id).'\').submit()">';
                echo '<option value="">'.TGlobal::OutHTML($oVariantType->fieldName).' wählen</option>';
                while ($oVariantValue = $oAvailableValues->Next()) {
                    $sSelected = '';
                    if ($oVariantValue->id == $sActiveValueForVariantType) {
                        $sSelected = 'selected="selected"';
                    }
                    echo '<option value="'.TGlobal::OutHTML($oVariantValue->id).'" '.$sSelected.'>'.TGlobal::OutHTML($oVariantValue->fieldName).'</option>';
                }
                echo '</select>';
                if (array_key_exists($oVariantType->id, $aSelectedTypeValues)) {
                    $aTmpSelectValue[$oVariantType->id] = $aSelectedTypeValues[$oVariantType->id];
                }
            }

            ?>
        </form>
    </div>
</div>