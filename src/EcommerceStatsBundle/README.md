# eCommerce Statistics Bundle

This bundle generates statistics reports and exposes them in multiple ways
in the backend.

After installation, ensure to create these sym links:

```bash
$ cd src/extensions/snippets-cms
$ ln -s ../../../vendor/chameleon-system/ecommerce-stats-bundle/src/Bundle/Resources/views/snippets-cms/ecommerceStats/
```

## Extending

There are currently 2 ways of adding statistics to the bundles output:

### 1. Implementing a custom `StatsProvider`
In order to add new stats through a StatsProvider, add a service that implements `StatsProviderInterface` and tag it with `chameleon_system_ecommerce_stats.stats_provider`.

```php
class MyStatsProvider implements StatsProviderInterface {

    public function addStatsToTable(
        StatsTableDataModel $statsTable,
        \DateTime $startDate,
        \DateTime $endDate,
        string $dateGroupType,
        string $portalId
    ) : StatsTableDataModel {
    
        $block = new StatsGroupDataModel('My Example Stats');
        $block->addRow([ 'pre sale' ], '2020-01-01', 22);
        $block->addRow([ 'evening sale' ], '2020-01-01', 33);
        $block->addRow([ 'pre sale' ], '2020-01-02', 133.5);
        $block->addRow([ 'evening sale' ], '2020-01-02', 185.8);

        $statsTable->addBlock('my_example_stats', $block);

        return $statsTable;
    }
    
}
```

```xml
<service
    id="my_custom_stats_provider"
    class="MyVendor\MyNamespace\StatsProvider\MyStatsProvider"
>
    <tag name="chameleon_system_ecommerce_stats.stats_provider" />
</service>

```

### 2. Adding a `pkg_shop_statistics_group` record

Statistics can also be added by adding a statistics group in the backend. This
configures a query that in turn fetches the statistics from the database.

The query should return at least the following keys:

* `sColumnName`: The name of the column (X-Axis)
* `dColumnValue`: The value corresponding to it (Y-Axis)

The query may contain the following placeholders, that will be replaced before
execution:

* `[{sColumnName}]` The query part that selects the name of the column. Should be used as follows: `SELECT [{sColumnName}] AS sColumnName`
* `[{sCondition}]`: Additional conditions including the `WHERE` keyword.

Example:

```sql
SELECT [{sColumnName}] AS sColumnName,
  `shop_order_item`.`order_amount` AS dColumnValue,
  shop_payment_method_name
FROM `shop_order`
LEFT JOIN `shop_order_item` 
    ON `shop_order`.`id` = `shop_order_item`.`shop_order_id`
[{sCondition}]
AND `shop_order`.`canceled` = '0'
ORDER BY datecreated
```
