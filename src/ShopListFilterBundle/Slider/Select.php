<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopListFilterBundle\Slider;

class Select
{
    /** @var bool */
    private $disabled = true;

    /** @var int[] */
    private $options = [];

    /** @var int */
    private $selectedOption;

    /**
     * @param bool $disabled
     *
     * @return Select
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function getDisabled()
    {
        return $this->disabled;
    }

    /**
     * @param int $selectedOption
     *
     * @return Select
     */
    public function setSelectedOption($selectedOption)
    {
        $this->selectedOption = $selectedOption;

        return $this;
    }

    /**
     * @return int
     */
    public function getSelectedOption()
    {
        return $this->selectedOption;
    }

    /**
     * @param int[] $options
     *
     * @return Select
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @param int $option
     *
     * @return Select
     */
    public function addOption($option)
    {
        $this->options[] = $option;

        return $this;
    }

    /**
     * @return int[]
     */
    public function getOptions()
    {
        return $this->options;
    }
}
