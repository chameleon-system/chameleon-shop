<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopListfilterMapper_FilterNumericSlider extends AbstractPkgShopListfilterMapper_Filter
{
    /**
     * {@inheritdoc}
     */
    public function Accept(
        IMapperVisitorRestricted $oVisitor,
        $bCachingEnabled,
        IMapperCacheTriggerRestricted $oCacheTriggerManager
    ): void {
        parent::Accept($oVisitor, $bCachingEnabled, $oCacheTriggerManager);

        /** @var $oFilterItem TPkgShopListfilterItemNumeric */
        $oFilterItem = $oVisitor->GetSourceObject('oFilterItem');
        /** @var $oActiveFilter TdbPkgShopListfilter */
        $oActiveFilter = $oVisitor->GetSourceObject('oActiveFilter');

        $userDataValueLow = false;
        $userDataValueHigh = false;
        $userData = $oActiveFilter->GetCurrentFilterAsArray();
        if (count($userData) > 0) {
            $dStartAmountTmp = $oFilterItem->GetActiveStartValue();
            if (!empty($dStartAmountTmp)) {
                $userDataValueLow = $dStartAmountTmp;
            }
            $dEndAmountTmp = $oFilterItem->GetActiveEndValue();
            if (false !== $dEndAmountTmp && 0 != $dEndAmountTmp) {
                $userDataValueHigh = $dEndAmountTmp;
            }
        }

        $highestArticlePrice = 0;
        $lowestArticlePrice = 0;
        $aArticlePrices = $oFilterItem->GetOptions();
        if (is_array($aArticlePrices)) {
            $aTmpArray = array_keys($aArticlePrices);
            if (is_array($aTmpArray) && count($aTmpArray)) {
                $highestArticlePrice = max($aTmpArray);
                $lowestArticlePrice = min($aTmpArray);
            }
        }

        $articleCount = count($aArticlePrices);

        $slider = new Slider();
        $selectFromPrice = new Select();
        $selectToPrice = new Select();

        if (0 == $articleCount) {
            /*
             * no articles
             * $lowestArticlePrice = $highestArticlePrice = 0.
             */
        } elseif (1 == $articleCount) {
            /**
             * 1 article
             * $lowestArticlePrice = $highestArticlePrice
             * slider
             *        range (values):
             *            - $lowestArticlePrice (rounded down)
             *        - $highestArticlePrice (rounded up)
             *        selected (min & max):
             *            - $lowestArticlePrice (rounded down)
             *        - $highestArticlePrice (rounded up)
             *            - *** IGNORE url input
             *        disabled:
             *            - true
             *        step:
             *            - $highestArticlePrice (rounded up) - $lowestArticlePrice (rounded down).
             */
            // TODO: psalm reports "InvalidCast: MapperVirtualSourceObject&static cannot be cast to int" here, but this should
            // not be the case, as $userDataValueHigh is a float here. We should investigate this further because usually psalm
            // knows what it's talking about.
            /** @psalm-suppress InvalidCast */
            $highestArticlePrice = $userDataValueHigh ? ((int) $userDataValueHigh) : $this->roundValueUp($highestArticlePrice);
            /** @psalm-suppress InvalidCast */
            $lowestArticlePrice = $userDataValueLow ? ((int) $userDataValueLow) : $this->roundValueDown($lowestArticlePrice);
            $slider->setDisabled(false)
                ->setValueLow($lowestArticlePrice)
                ->setValueHigh($highestArticlePrice)
                ->setMin($lowestArticlePrice)
                ->setMax($highestArticlePrice)
                ->setStep(0);

            if (false != $userDataValueLow) {
                /* @psalm-suppress InvalidCast */
                $selectFromPrice
                    ->addOption((int) $userDataValueLow)
                    ->setSelectedOption($userDataValueLow);
            } else {
                $selectFromPrice
                    ->addOption($lowestArticlePrice)
                    ->addOption($highestArticlePrice)
                    ->setSelectedOption($lowestArticlePrice);
            }

            if (false != $userDataValueHigh) {
                /* @psalm-suppress InvalidCast */
                $selectToPrice
                    ->addOption((int) $userDataValueHigh)
                    ->setSelectedOption($userDataValueHigh);
            } else {
                $selectToPrice
                    ->addOption($lowestArticlePrice)
                    ->addOption($highestArticlePrice)
                    ->setSelectedOption($highestArticlePrice);
            }
        } else {
            /**
             * at least 2.
             */
            $highestArticlePrice = $this->roundValueUp($highestArticlePrice);
            $lowestArticlePrice = $this->roundValueDown($lowestArticlePrice);

            $stepCount = 20;

            $delta = $highestArticlePrice - $lowestArticlePrice;
            $stepSize = \floor($delta / $stepCount);
            if ($stepSize < 1) {
                $stepSize = 1;
                // $stepCount = round($delta); // delta is nearly an integer: avoid floating rounding errors here
            }
            $stepCount = \ceil($delta / $stepSize); // consider now a slightly different step count due to rounded step size

            for ($i = 0; $i <= $stepCount; ++$i) {
                $priceOption = round($lowestArticlePrice + $i * $stepSize);
                if (false != $userDataValueLow && $userDataValueLow < $priceOption) {
                    /* @psalm-suppress InvalidCast */
                    $selectFromPrice->addOption((int) $userDataValueLow);
                }
                /* @psalm-suppress InvalidCast */
                if (false != $userDataValueHigh
                    && ($userDataValueHigh < $priceOption || $i == $stepCount)
                ) {
                    $selectToPrice->addOption((int) $userDataValueHigh);
                }
                $selectFromPrice->addOption($priceOption);
                $selectToPrice->addOption($priceOption);
            }
            /**
             * as the $stepSize is rounded
             * ($lowestArticlePrice + ($stepCount * $stepSize) can be higher than the actual $highestArticlePrice
             * this would mess up the behaviour between the slider and the selects.
             *
             * => set $highestArticlePrice based on last option element in $selectToPrice
             */
            $options = $selectToPrice->getOptions();
            $highestArticlePrice = end($options);

            $slider->setDisabled(false)
                ->setValueLow($userDataValueLow ?: $lowestArticlePrice)
                ->setValueHigh($userDataValueHigh ?: $highestArticlePrice)
                ->setMin($lowestArticlePrice)
                ->setMax($highestArticlePrice)
                ->setStep($stepSize);

            $selectFromPrice->setSelectedOption($userDataValueLow ?: $lowestArticlePrice)
                ->setDisabled($userDataValueLow ? false : true);
            $selectToPrice->setSelectedOption($userDataValueHigh ?: $highestArticlePrice)
                ->setDisabled($userDataValueHigh ? false : true);
        }

        $oVisitor->SetMappedValue('slider', $slider);
        $oVisitor->SetMappedValue('selectFromPrice', $selectFromPrice);
        $oVisitor->SetMappedValue('selectToPrice', $selectToPrice);
    }

    /**
     * @param float $value
     *
     * @return float
     */
    protected function roundValueUp($value)
    {
        if ($value > 10000) {
            $value = round($value / 1000, 3);
            $value = ceil($value);
            $value = $value * 1000;
        } elseif ($value > 100 && $value < 10000) {
            $value = round($value / 100, 1);
            $value = ceil($value);
            $value = $value * 100;
        } else {
            $value = ceil($value);
        }

        return $value;
    }

    /**
     * @param float $value
     *
     * @return float
     */
    protected function roundValueDown($value)
    {
        if ($value > 10000) {
            $value = floor($value / 1000) * 1000;
        } elseif ($value > 1000 && $value < 10000) {
            $value = floor($value / 100) * 100;
        } else {
            $value = floor($value / 10) * 10;
        }

        return $value;
    }
}

class Slider
{
    /**
     * String representation of the boolean.
     *
     * @var string
     *
     * @psalm-var 'true'|'false'
     */
    private $disabled = 'false';
    /** @var int */
    private $valueLow;
    /** @var int */
    private $valueHigh;
    /** @var int */
    private $min;
    /** @var int */
    private $max;
    /** @var int */
    private $step;

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
     * @return string
     *
     * @psalm-return 'true'|'false'
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
        if (false === in_array($option, $this->options)) {
            $this->options[] = $option;
        }

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
