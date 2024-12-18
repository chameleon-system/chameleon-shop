<?php

declare(strict_types=1);

namespace ChameleonSystem\EcommerceStats\Tests\Architecture;

use PhpAT\Rule\Rule;
use PhpAT\Selector\Selector;
use PhpAT\Test\ArchitectureTest;

class LibraryBundleSeparationTest extends ArchitectureTest
{
    public function testLibraryShouldNotDependOnBundle(): Rule
    {
        return $this->newRule
            ->classesThat(Selector::haveClassName('ChameleonSystem\\EcommerceStats\\*'))
            ->mustNotDependOn()
            ->classesThat(Selector::haveClassName('ChameleonSystem\\EcommerceStatsBundle\\*'))
            ->build();
    }
}
