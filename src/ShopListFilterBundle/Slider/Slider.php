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

class Slider
{
    /** @var bool */
    private $disabled = 'false';
    /** @var int */
    private $valueLow = null;
    /** @var int */
    private $valueHigh = null;
    /** @var int */
    private $min = null;
    /** @var int */
    private $max = null;
    /** @var int */
    private $step = null;

    /**
     * @param bool $disabled
     *
     * @return Slider
     */
    public function setDisabled($disabled)
    {
        // slider need that as string
        $this->disabled = ($disabled) ? 'true' : 'false';

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
     * @param int $max
     *
     * @return Slider
     */
    public function setMax($max)
    {
        $this->max = $max;

        return $this;
    }

    /**
     * @return int
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @param int $min
     *
     * @return Slider
     */
    public function setMin($min)
    {
        $this->min = $min;

        return $this;
    }

    /**
     * @return int
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * @param int $step
     *
     * @return Slider
     */
    public function setStep($step)
    {
        $this->step = $step;

        return $this;
    }

    /**
     * @return int
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * @param int $valueHigh
     *
     * @return Slider
     */
    public function setValueHigh($valueHigh)
    {
        $this->valueHigh = $valueHigh;

        return $this;
    }

    /**
     * @return int
     */
    public function getValueHigh()
    {
        return $this->valueHigh;
    }

    /**
     * @param int $valueLow
     *
     * @return Slider
     */
    public function setValueLow($valueLow)
    {
        $this->valueLow = $valueLow;

        return $this;
    }

    /**
     * @return int
     */
    public function getValueLow()
    {
        return $this->valueLow;
    }
}
