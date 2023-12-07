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

interface VariantTypeDataModelInterface
{
    public function getTitle(): string;

    public function getSystemName(): string;

    public function getImageId(): string;

    public function isSelectionAllowed(): bool;

    public function getVariantTypeValues(): array;

    public function setVariantTypeValues(array $variantTypeValues): void;

    public function getAllPropertiesAsArray(): array;
}
