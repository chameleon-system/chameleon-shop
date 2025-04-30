<?php
/** @var $oCategory TdbShopCategory */
?>

<div class="category">
    <h1><?php echo TGlobal::OutHTML($oCategory->GetName()); ?></h1>
    <?php $sCatText = $oCategory->GetTextField('description');
if (!empty($sCatText)) {
    echo $sCatText;
} ?>
</div>