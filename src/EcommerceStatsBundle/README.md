# Chameleon System EcommerceStatsBundle
=====================================

## Overview
The EcommerceStatsBundle collects and displays e-commerce metrics in the Chameleon backend. It supports custom data providers, database-driven statistic groups, and dashboard widgets for insightful reporting.

Key Features
------------
- **Custom StatsProvider**: implement `StatsProviderInterface` and tag with `chameleon_system_ecommerce_stats.stats_provider` to programmatically add statistics.
- **SQL-Based Groups**: configure `pkg_shop_statistics_group` records with parameterized SQL queries for database-driven stats.
- **Dashboard Widgets**: visualize statistic groups on the Chameleon Dashboard with configurable timeframes.
- **Flexible Date Grouping**: group data by day, week, month, or year.
- **Extensible UI**: integrates with Twig and ViewRenderer for seamless backend display.

Installation
------------
This bundle is included in `chameleon-system/chameleon-shop` and auto-registered via Symfony.
No additional Composer installation is needed.
To register manually (or without without Flex), add to `app/AppKernel.php`:
```php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = [
        // ...
        new ChameleonSystem\EcommerceStatsBundle\ChameleonSystemEcommerceStatsBundle(),
    ];
    return $bundles;
}
```
Clear cache:
```bash
php bin/console cache:clear
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
    
        $block = new StatsGroupDataModel('My Example Stats', 'my_example_stats');
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

* `[{sColumnName}]`: The query part that selects the name of the column. Should be used as follows: `SELECT [{sColumnName}] AS sColumnName`
* `[{sCondition}]`: Additional conditions including the `WHERE` keyword.
* `<trans>field</trans>`: You can mark any field like this to have it translated to the active language. 

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

## Dashboard Widgets

The bundle also provides dashboard widgets that are registered in the Chameleon Dashboard.

You can set a timeframe for the widget stats by adding a parameter in your parameters.yml or env vars
like: `chameleon_system_core_dashboard_default_timeframe: '-30 days'` (be sure to set it to a minus value used in DateTime::modify)

## License
This bundle is released under the same license as the Chameleon System. See the LICENSE file in the project root.
