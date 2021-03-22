<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\EcommerceStatsBundle\DataModel;

class ShopOrderItemDataModel
{
    /**
     * @var string
     */
    private $articlenumber;

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $totalOrdered;

    /**
     * @var float
     */
    private $totalOrderedValue;

    /**
     * @var string
     */
    private $categoryPath;

    public function __construct(
        string $articlenumber,
        string $name,
        int $totalOrdered,
        float $totalOrderedValue,
        string $categoryPath
    ) {
        $this->articlenumber = $articlenumber;
        $this->name = $name;
        $this->totalOrdered = $totalOrdered;
        $this->totalOrderedValue = $totalOrderedValue;
        $this->categoryPath = $categoryPath;
    }

    public function getArticlenumber(): string
    {
        return $this->articlenumber;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTotalOrdered(): int
    {
        return $this->totalOrdered;
    }

    public function getTotalOrderedValue(): float
    {
        return $this->totalOrderedValue;
    }

    public function getCategoryPath(): string
    {
        return $this->categoryPath;
    }
}
