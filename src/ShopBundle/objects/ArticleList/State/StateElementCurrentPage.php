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

use ChameleonSystem\ShopBundle\objects\ArticleList\Exceptions\InvalidPageNumberException;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateElementInterface;

/**
 * @implements StateElementInterface<positive-int|0|null>
 */
class StateElementCurrentPage implements StateElementInterface
{
    /**
     * @return string
     */
    public function getKey()
    {
        return 'p';
    }

    public function validate($value)
    {
        if (false === is_numeric($value)) {
            throw new InvalidPageNumberException('current page must be numeric. given: '.$value);
        }

        $intVal = intval($value);

        if ($intVal != $value) {
            throw new InvalidPageNumberException('current page must be integer. given: '.$value);
        }

        if ($intVal < 0) {
            throw new InvalidPageNumberException('current page must be >= 0. given: '.$value);
        }

        return true;
    }

    public function normalize($value)
    {
        $num = intval($value);
        if ($num < 0) {
            $num = 0;
        }

        if (0 === $num) {
            return null;
        }

        return $num;
    }
}
