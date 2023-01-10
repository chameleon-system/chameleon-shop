<?php
/** @var $oGroup MTShopStatistic_Statblock */
// show header first
$aColumNames = $oGroup->GetColumnNames();
$iDepth = $oGroup->GetColumnGroupDepth();
$oLocal = TCMSLocal::GetActive();
?>
<tr class="headerRow">
    <?php
    for ($i = 0; $i < $iDepth; ++$i) {
        echo '<th class="colHeader">&nbsp;</th>';
    }
    //echo $oGroup->Render('export/htmlHeader');
    foreach ($aColumNames as $sColName) {
        echo '<th class="colHeader">'.TGlobal::OutHTML($sColName).'</th>';
    }
    ?>
    <th class="colHeader"><?=TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.cms_module_shop_statistic.column_total')); ?></th>
</tr>
<tr>
    <th><?=$oGroup->sGroupName; ?></th>
    <?php for ($i = 1; $i < $iDepth; ++$i) {
        echo '<th>&nbsp</th>';
    } ?>
    <?php
    $dSumme = 0;
    reset($aColumNames);
    $dOldVal = 0;
    foreach ($aColumNames as $sCol) {
        $dVal = $oGroup->GetColumn($sCol);
        $dSumme += $dVal;
        $sVal = '';
        if (!empty($dVal)) {
            $sVal = $oLocal->FormatNumber($dVal, 2);
            if (!empty($dVal) && !empty($dOldVal)) {
                $sClass = 'color:red';
                if ($dOldVal > 0) {
                    $dDiff = (($dVal - $dOldVal) / $dOldVal);
                } else {
                    $dDiff = 0;
                }
                if (0 !== round($dDiff * 100, 2)) {
                    if ($dDiff > 0) {
                        $dDiff = '+'.$oLocal->FormatNumber($dDiff * 100, 2);
                        $sClass = 'color:green';
                    } else {
                        $dDiff = $oLocal->FormatNumber($dDiff * 100, 2);
                    }
                    if ($bShowChange) {
                        $sVal .= '<br />(<span style="'.$sClass.';white-space:white-space: nowrap;">'.$dDiff.'%</span>)';
                    }
                }
            }
        }
        echo '<td>'.$sVal.'</td>';
        $dOldVal = $dVal;
    }
    ?>
    <td><?php if (!empty($dSumme)) {
        echo $oLocal->FormatNumber($dSumme, 2);
    } ?></td>
</tr>
<?php
// now work through sub groups - should make this recursive
foreach ($aSubGroups as $oSubGroup) {
    $dSumme = 0;
    echo '<tr>';
    echo '<th></th>';
    echo '<th>'.TGlobal::OutHTML($oSubGroup->sGroupName).'</th>';
    reset($aColumNames);
    $dOldVal = 0;
    foreach ($aColumNames as $sCol) {
        $dVal = $oSubGroup->GetColumn($sCol);
        $dSumme += $dVal;
        $sVal = '';
        if (!empty($dVal)) {
            $sVal = $oLocal->FormatNumber($dVal, 2);
            if (!empty($dVal) && !empty($dOldVal)) {
                $sClass = 'color:red';
                if ($dOldVal > 0) {
                    $dDiff = (($dVal - $dOldVal) / $dOldVal);
                } else {
                    $dDiff = 0;
                }
                if (0 !== round($dDiff * 100, 2)) {
                    if ($dDiff > 0) {
                        $dDiff = '+'.$oLocal->FormatNumber($dDiff * 100, 2);
                        $sClass = 'color:green';
                    } else {
                        $dDiff = $oLocal->FormatNumber($dDiff * 100, 2);
                    }
                    if ($bShowChange) {
                        $sVal .= '<br />(<span style="'.$sClass.';white-space:white-space: nowrap;">'.$dDiff.'%</span>)';
                    }
                }
            }
        }
        echo '<td>'.$sVal.'</td>';
        $dOldVal = $dVal;
    }
    echo '<td>';
    if (!empty($dSumme)) {
        echo $oLocal->FormatNumber($dSumme, 2);
    }
    echo '</td>';
    echo '</tr>';
}

?>
