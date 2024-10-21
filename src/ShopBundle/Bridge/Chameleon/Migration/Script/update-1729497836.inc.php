<h1>Build #1729497833</h1>
<h2>Date: 2024-10-21</h2>
<div class="changelog">
    - #64747: product images and preview images: change fieldtype to CMSFIELD_PROPERTY_MEDIA, so a direct upload is possible
</div>
<?php

$dbConnection = TCMSLogChange::getDatabaseConnection();

$query = "SELECT `cms_field_conf`.`id`, `cms_field_conf`.`fieldtype_config`
            FROM `cms_field_conf` 
           WHERE `cms_tbl_conf_id` = :belongsToTable 
             AND `name` = :fieldname
             AND `cms_field_type_id` = :fieldtype";

$row = $dbConnection->fetchAssociative($query,
    [
        'belongsToTable' => TCMSLogChange::GetTableId('shop_article'),
        'fieldname' => 'shop_article_image', //Artikeldetailbilder
        'fieldtype' => TCMSLogChange::GetFieldType('CMSFIELD_PROPERTY')
    ]);

if (false !== $row) {
    $fieldConf = $row['fieldtype_config'];
    if ('' !== $fieldConf) {
        $fieldConf .= '\n';
    }
    $fieldConf .= 'bShowCategorySelector=1\nsMediaTargetFieldName=cms_media_id';

    $data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
        ->setFields([
            'cms_field_type_id' => TCMSLogChange::GetFieldType('CMSFIELD_PROPERTY_MEDIA'),
            'fieldtype_config' => $fieldConf,
        ])
        ->setWhereEquals([
            'id' => $row['id'],
        ])
    ;
    TCMSLogChange::update(__LINE__, $data);
}


$row = $dbConnection->fetchAssociative($query,
    [
        'belongsToTable' => TCMSLogChange::GetTableId('shop_article'),
        'fieldname' => 'shop_article_preview_image', //Artikelvorschaubilder
        'fieldtype' => TCMSLogChange::GetFieldType('CMSFIELD_PROPERTY')
    ]);

if (false !== $row) {
    $fieldConf = $row['fieldtype_config'];
    if ('' !== $fieldConf) {
        $fieldConf .= '\n';
    }
    $fieldConf .= 'bShowCategorySelector=1\nsMediaTargetFieldName=cms_media_id';

    $data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
        ->setFields([
            'cms_field_type_id' => TCMSLogChange::GetFieldType('CMSFIELD_PROPERTY_MEDIA'),
            'fieldtype_config' => $fieldConf,
        ])
        ->setWhereEquals([
            'id' => $row['id'],
        ])
    ;
    TCMSLogChange::update(__LINE__, $data);
}
