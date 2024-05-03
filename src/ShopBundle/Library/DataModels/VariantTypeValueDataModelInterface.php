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

interface VariantTypeValueDataModelInterface
{
    public function getTitle(): string;

    public function getColor(): string;

    public function getImageId(): string;

    public function isActive(): bool;

    public function getSelectLink(): string;

    public function isSelectable(): bool;

    public function setSelectable(bool $selectable): void;

    public function getAllPropertiesAsArray(): array;
}
