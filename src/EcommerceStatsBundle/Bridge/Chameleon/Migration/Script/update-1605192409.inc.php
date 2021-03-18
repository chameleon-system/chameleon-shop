<h1>Build #1605192409</h1>
<h2>Date: 2020-09-25</h2>
<div class="changelog">
    - ref #50493: Add a composite index key for faster requests
</div>
<?php

$rows = TCMSLogChange::getDatabaseConnection()
    ->executeQuery('SHOW INDEX FROM `shop_order`')
    ->fetchAll(\Doctrine\DBAL\FetchMode::ASSOCIATIVE);
$indices = array_column($rows, 'Key_name');

// Note: dropping index to recreate it in order to guarantee that the index
// has the same format in all instances, no matter how it was formed before.
if (in_array('stat_request_idx', $indices)) {
    $query = 'ALTER TABLE `shop_order` DROP INDEX `stat_request_idx`;';
    TCMSLogChange::RunQuery(__LINE__, $query);
}

$query = 'ALTER TABLE `shop_order` ADD INDEX `stat_request_idx` (`canceled`, `datecreated`, `shop_payment_method_name`) USING BTREE;';
TCMSLogChange::RunQuery(__LINE__, $query);
