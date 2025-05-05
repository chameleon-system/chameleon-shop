<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces;

/**
 * @template TNormalizedValue
 */
interface StateElementInterface
{
    /**
     * @return string
     */
    public function getKey();

    /**
     * @return bool
     *
     * @psalm-assert TNormalizedValue $value
     *
     * @throws \ChameleonSystem\ShopBundle\objects\ArticleList\Exceptions\StateParameterException
     */
    public function validate($value);

    /**
     * @return TNormalizedValue
     */
    public function normalize($value);
}
