<?php
/** @var $oListItem TdbPkgShopListfilterItem */
$oListFilter = TdbPkgShopListfilter::GetActiveInstance();
$aData = $oListFilter->GetCurrentFilterAsArray();
$oLocal = TCMSLocal::GetActive();
$aValues = $oListItem->GetOptions();
if (is_array($aValues) && count($aValues) > 0) {
    $aTmpArray = array_flip($aValues);
    $dHighestValue = max($aTmpArray);
    $aRangeStartOptions = [];
    $aRangeStartOptions[] = ['start' => 0, 'end' => 10, 'count' => 0];
    $aRangeStartOptions[] = ['start' => 10, 'end' => 20, 'count' => 0];
    $aRangeStartOptions[] = ['start' => 20, 'end' => 30, 'count' => 0];
    $aRangeStartOptions[] = ['start' => 30, 'end' => 40, 'count' => 0];
    $aRangeStartOptions[] = ['start' => 40, 'end' => 50, 'count' => 0];
    $aRangeStartOptions[] = ['start' => 50, 'end' => 75, 'count' => 0];
    $aRangeStartOptions[] = ['start' => 75, 'end' => 100, 'count' => 0];
    $aRangeStartOptions[] = ['start' => 100, 'end' => 150, 'count' => 0];
    $aRangeStartOptions[] = ['start' => 150, 'end' => 200, 'count' => 0];
    $aRangeStartOptions[] = ['start' => 200, 'end' => $dHighestValue, 'count' => 0];

    $aRangeStartOptions = array_reverse($aRangeStartOptions, true);

    $aRangKeys = array_keys($aRangeStartOptions);

    $aRangeItemsWithValueLargerZero = [];
    foreach ($aValues as $sValue => $iCount) {
        reset($aRangKeys);
        $bFound = false;
        while (!$bFound && false !== ($iRangeIndex = current($aRangKeys))) {
            if ($aRangeStartOptions[$iRangeIndex]['start'] <= $sValue) {
                $aRangeStartOptions[$iRangeIndex]['count'] += $iCount;
                if ($aRangeStartOptions[$iRangeIndex]['count'] > 0) {
                    $aRangeItemsWithValueLargerZero[$iRangeIndex] = 1;
                }
                $bFound = true;
            }
            $tmp = next($aRangKeys);
        }
    }
    reset($aRangeStartOptions);
    $aRangeStartOptions = array_reverse($aRangeStartOptions, true);

    $sLongCSS = '';
    if (count($aRangeItemsWithValueLargerZero) > 7) {
        $sLongCSS = 'longValueItemList';
    }
    $dStartValue = $oListItem->GetActiveStartValue();
    $dEndValue = $oListItem->GetActiveEndValue(); ?>
<?php if (count($aRangeStartOptions) > 1) {
        ?>
    <div class="TPkgShopListfilterItem <?php echo get_class($oListItem); ?>">
        <div class="numeric">
            <div class="listFilterName"><?php echo TGlobal::OutHTML($oListItem->fieldName); ?></div>
            <div class="<?php if ($oListItem->GetActiveStartValue() > 0 || $oListItem->GetActiveEndValue() > 0) {
                echo 'valueitems_high';
            } else {
                echo 'valueitems';
            } ?> <?php echo $sLongCSS; ?>">
                <ul>

                    <?php

                        $bHasSelection = false;
        foreach ($aRangeStartOptions as $iRangeIndex => $aRangeData) {
            if ($aRangeData['count'] > 0) {
                $sActive = '';
                if ((false !== $dStartValue && $aRangeData['start'] <= $dStartValue) && (false !== $dEndValue && $aRangeData['end'] >= $dEndValue)) {
                    $bHasSelection = true;
                    $sActive = 'active';
                }
                echo '<li class="'.$sActive.'"><a href="'.$oListItem->GetAddFilterURL([TPkgShopListfilterItemNumeric::URL_PARAMETER_FILTER_START_VALUE => $aRangeData['start'], TPkgShopListfilterItemNumeric::URL_PARAMETER_FILTER_END_VALUE => $aRangeData['end']]).'" rel="nofollow">€ '.TGlobal::OutHTML($oLocal->FormatNumber($aRangeData['start'])).' - € '.TGlobal::OutHTML($oLocal->FormatNumber($aRangeData['end'])).'</a></li>';
            }
        }
        if ($bHasSelection) {
            echo '<li><a href="'.$oListItem->GetAddFilterURL([]).'" rel="nofollow">zurücksetzen</a></li>';
        } ?>
                </ul>
            </div>
        </div>
    </div>
    <?php
    }
}?>