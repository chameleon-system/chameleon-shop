<?php declare(strict_types=1);

namespace ChameleonSystem\ShopListFilterBundle\Tests\mappers\filter\Mocks;

use IMapperVisitorRestricted;

class VisitorMock implements IMapperVisitorRestricted
{
    public $mappedValues = [];
    public $sourceObjects = [];

    public function __construct(array $sourceObjects)
    {
        $this->sourceObjects = $sourceObjects;
    }

    public function SetMappedValue($key, $value)
    {
        $this->mappedValues[$key] = $value;
    }

    public function SetMappedValueFromArray($aData)
    {
        $this->mappedValues = array_replace(
            $this->mappedValues,
            $aData
        );
    }

    public function GetSourceObject($key)
    {
        return $this->sourceObjects[$key] ?? null;
    }

    public function getSnippetName()
    {
        return '';
    }

    public function runMapperChainOn($mapperChainName, array $mapperInputData)
    {
        throw new \RuntimeException('not implemented');
    }
}
