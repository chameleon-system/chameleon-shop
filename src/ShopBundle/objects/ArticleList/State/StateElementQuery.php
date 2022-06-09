<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\objects\ArticleList\State;

use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateElementInterface;

/**
 * @implements StateElementInterface<mixed>
 */
class StateElementQuery implements StateElementInterface
{
    /**
     * @return string
     */
    public function getKey()
    {
        return 'q';
    }

    public function validate($value)
    {
        return true;
    }

    public function normalize($value)
    {
        return $value;
    }
}
