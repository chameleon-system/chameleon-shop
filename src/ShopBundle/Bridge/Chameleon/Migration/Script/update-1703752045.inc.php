<h1>Build #1703752045</h1>
<h2>Date: 2023-12-28</h2>
<div class="changelog">
    - ref #59244: fix property fields. Some property fields had the owner set to a plain value select field,
    instead of a parent field. That will not work for doctrine entities.
</div>
<?php


$tableFieldsToTransform = [
    'data_extranet_user' => ['shop_id',],
    'shop_order' => ['data_extranet_user_id',],
    'shop_suggest_article_log' => ['data_extranet_user_id',],
];

foreach ($tableFieldsToTransform as $tableName => $fields) {
    foreach ($fields as $field) {
        $data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
            ->setFields([
                'cms_field_type_id' => TCMSLogChange::GetFieldType('CMSFIELD_PROPERTY_PARENT_ID'),
            ])
            ->setWhereEquals([
                'id' => TCMSLogChange::GetTableFieldId(TCMSLogChange::GetTableId($tableName), $field),
            ]);
        TCMSLogChange::update(__LINE__, $data);
    }
}
