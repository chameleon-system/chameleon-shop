<?php
/** @var $oGroup MTShopStatistic_Statblock */
/** @var $aColumnNames array */
/** @var $sLevel int */
// show header first

?>
<tr>
    <?php
    for ($i = 0; $i <= $sLevel; ++$i) {
        echo '<th>&nbsp;</th>';
    }
    echo '<th>'.$oGroup->sGroupName.'</th>';
    ?>
</tr>
