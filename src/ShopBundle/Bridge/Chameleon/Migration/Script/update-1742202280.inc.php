<h1>Build #1742202280</h1>
<h2>Date: 2025-04-24</h2>
<div class="changelog">
    - ref #66402: add index on shop_order.customer_number if missing
</div>
<?php
$connection = TCMSLogChange::getDatabaseConnection();

$indexName = 'customer_number';
$tableName = 'shop_order';

$indexExists = $connection->fetchOne("
    SELECT COUNT(1)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = ?
    AND INDEX_NAME = ?
", [$tableName, $indexName]);

if (!$indexExists) {
    $connection->executeStatement("ALTER TABLE `".$tableName."` ADD INDEX `".$indexName."` (`customer_number`)");
}
