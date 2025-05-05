<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\Library\DataModels;

class VariantTypeDataModel implements VariantTypeDataModelInterface
{
    private string $title;
    private string $systemName;
    private string $imageId;
    private bool $isSelectionAllowed;
    private array $variantTypeValues = [];

    public function __construct(
        string $title,
        string $systemName,
        string $imageId,
        bool $isSelectionAllowed
    ) {
        $this->title = $title;
        $this->systemName = $systemName;
        $this->imageId = $imageId;
        $this->isSelectionAllowed = $isSelectionAllowed;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSystemName(): string
    {
        return $this->systemName;
    }

    public function getImageId(): string
    {
        return $this->imageId;
    }

    public function isSelectionAllowed(): bool
    {
        return $this->isSelectionAllowed;
    }

    public function getVariantTypeValues(): array
    {
        return $this->variantTypeValues;
    }

    public function setVariantTypeValues(array $variantTypeValues): void
    {
        $this->variantTypeValues = $variantTypeValues;
    }

    /**
     * @note this is for backwards compatibility only.
     */
    public function getAllPropertiesAsArray(): array
    {
        return [
            'sTitle' => $this->title,
            'sSystemName' => $this->systemName,
            'cms_media_id' => $this->imageId,
            'bAllowSelection' => $this->isSelectionAllowed,
            'aItems' => [],
        ];
    }
}
