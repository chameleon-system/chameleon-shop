<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\EcommerceStats\Tests\Unit\DataModel;

use ChameleonSystem\EcommerceStats\DataModel\StatsGroupDataModel;
use PHPUnit\Framework\TestCase;

class EcommerceStatsGroupTest extends TestCase
{
    public function testAddsCategoryGroupingElementsWhenAddingMoreThanOneCategoryInDepth(): void
    {
        $group = new StatsGroupDataModel();
        $group->addRow(['foo', 'bar'], 'test', 1);
        $group->addRow(['foo', 'baz'], 'test', 2);
        $group->addRow(['foo', 'bar', 'baz'], 'test', 4);

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
