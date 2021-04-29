<?php declare(strict_types=1);

namespace ChameleonSystem\ShopListFilterBundle\Tests\mappers\filter;

use ChameleonSystem\ShopListFilterBundle\Tests\mappers\filter\Mocks\VisitorMock;
use IMapperCacheTriggerRestricted;
use TPkgShopListfilterMapper_FilterNumericSlider;
use PHPUnit\Framework\TestCase;

class TPkgShopListfilterMapper_FilterNumericSliderTest extends TestCase
{
    public function provideMinAndMax(): array
    {
        return [
            [ 30, 94, 100 ],
            [ 20, 50, 60 ],
            [ 0, 10, 15 ],
        ];
    }

    /** @dataProvider provideMinAndMax */
    public function testSetsMaxValueOfSliderToAtLeastMaxValueOfData(
        int $min,
        int $max,
        int $expectedMaxBound
    ): void {
        $mapper = new TPkgShopListfilterMapper_FilterNumericSlider();

        $visitor = new VisitorMock([
            'oFilterItem' => new class ($min, $max) {
                public $fieldName = 'foo';
                private $options;

                public function __construct(int $min, int $max)
                {
                    $this->options = [ $min => 1, $max => 1];
                }

                public function GetURLInputName() { return ''; }
                public function GetAddFilterURL() { return ''; }
                public function IsActive() { return true; }
                public function GetActiveStartValue() { return 0; }
                public function GetActiveEndValue() { return 0; }
                public function GetOptions() { return $this->options; }
            },
            'oActiveFilter' => new class {
                public function GetCurrentFilterAsArray() {
                    return [ ];
                }
            }
        ]);

        $oCacheTriggerManager = $this->createMock(IMapperCacheTriggerRestricted::class);

        $mapper->Accept($visitor, false, $oCacheTriggerManager);

        /** @var \Slider $slider */
        $slider = $visitor->mappedValues['slider'];
        $this->assertGreaterThanOrEqual($max, $slider->getMax());
        $this->assertLessThanOrEqual($expectedMaxBound, $slider->getMax());
    }
}
