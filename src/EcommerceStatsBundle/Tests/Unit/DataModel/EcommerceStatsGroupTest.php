<?php

declare(strict_types=1);

namespace ChameleonSystem\EcommerceStatsBundle\Tests\Unit\DataModel;

use ChameleonSystem\EcommerceStatsBundle\DataModel\StatsGroupDataModel;
use PHPUnit\Framework\TestCase;

class EcommerceStatsGroupTest extends TestCase
{
    public function testAddsCategoryGroupingElementsWhenAddingMoreThanOneCategoryInDepth(): void
    {
        $group = new StatsGroupDataModel();
        $group->addRow(['foo', 'bar'], ['sColumnName' => 'test', 'dColumnValue' => 1]);
        $group->addRow(['foo', 'baz'], ['sColumnName' => 'test', 'dColumnValue' => 2]);
        $group->addRow(['foo', 'bar', 'baz'], ['sColumnName' => 'test', 'dColumnValue' => 4]);

        $subGroups = $group->getSubGroups();

        // All elements tagged with both foo&bar (['foo', 'bar']=1 + ['foo', 'bar', 'baz']=4)
        $this->assertEquals(5, $subGroups['foo']->getSubGroups()['bar']->getTotals('test'));

        // All elements tagged with both foo&baz (['foo', 'baz']=2 + ['foo','bar','baz']=4)
        $this->assertEquals(6, $subGroups['foo']->getSubGroups()['baz']->getTotals('test'));

        // All elements tagged with foo (['foo', 'bar']=1 + ['foo', 'baz']=2 + ['foo','bar','baz']=4)
        $this->assertEquals(7, $subGroups['foo']->getTotals('test'));

        // All elements tagged with foo&bar&baz (['foo','bar','baz']=4)
        $this->assertEquals(4, $subGroups['foo']->getSubGroups()['bar']->getSubGroups()['baz']->getTotals('test'));
    }
}
