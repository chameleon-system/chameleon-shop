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

class VariantTypeValueDataModel implements VariantTypeValueDataModelInterface
{
    private string $title;
    private string $color;
    private string $imageId;
    private bool $isActive;
    private string $selectLink;
    private bool $selectable;

    public function __construct(
        string $title,
        string $color,
        string $imageId,
        bool $isActive,
        string $selectLink,
        bool $selectable
    ) {
        $this->title = $title;
        $this->color = $color;
        $this->imageId = $imageId;
        $this->isActive = $isActive;
        $this->selectLink = $selectLink;
        $this->selectable = $selectable;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getImageId(): string
    {
        return $this->imageId;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getSelectLink(): string
    {
        return $this->selectLink;
    }

    public function isSelectable(): bool
    {
        return $this->selectable;
    }

    public function setSelectable(bool $selectable): void
    {
        $this->selectable = $selectable;
    }

    /**
     * @note this is for backwards compatibility only.
     */
    public function getAllPropertiesAsArray(): array
    {
        return [
            'sTitle' => $this->title,
            'sColor' => $this->color,
            'cms_media_id' => $this->imageId,
            'bIsActive' => $this->isActive,
            'sSelectLink' => $this->selectLink,
            'bArticleIsActive' => $this->selectable ? '1' : '0',
        ];
    }
}
