<h1>Build <div id="17"></div></h1>
<h2>Date: 2025-03-17</h2>
<div class="changelog">
    - ref #66055: removed outdated smart url handler
</div>
<?php

$outDatedSmartUrlHandler = [
    'TCMSSmartURLHandler_ShopPaymentSofortueberweisungAPI',
];

$connection = TCMSLogChange::getDatabaseConnection();

foreach ($outDatedSmartUrlHandler as $smartUrlHandler) {
    $smartUrlHandlerId = $connection->fetchOne(
        'SELECT id FROM `cms_smart_url_handler` WHERE name = :name',
        ['name' => $smartUrlHandler]
    );
    if(false === $smartUrlHandlerId) {
        continue;
    }

    $tableEditor = \TTools::GetTableEditorManager('cms_smart_url_handler');
    $tableEditor->AllowDeleteByAll(true);

    //Delete everything references
    $tableEditor->Delete($smartUrlHandlerId);
}