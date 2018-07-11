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

interface StateElementInterface
{
    public function getKey();

    /**
     * @param $value
     *
     * @return bool
     *
     * @throws \ChameleonSystem\ShopBundle\objects\ArticleList\Exceptions\StateParameterException
     */
    public function validate($value);

    public function normalize($value);
}
