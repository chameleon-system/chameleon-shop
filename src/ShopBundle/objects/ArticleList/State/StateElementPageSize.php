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

use ChameleonSystem\ShopBundle\objects\ArticleList\Exceptions\InvalidPageSizeException;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateElementInterface;

/**
 * @implements StateElementInterface<positive-int|0|-1|null|false>
 */
class StateElementPageSize implements StateElementInterface
{
    /**
     * @var list<int|null>
     */
    private $validOptions;

    /**
     * @return string
     */
    public function getKey()
    {
        return 'ps';
    }

    public function validate($value)
    {
        if (false === is_numeric($value)) {
            throw new InvalidPageSizeException("Page size must be numeric. Given: $value");
        }

        $intVal = intval($value);

        if ($intVal != $value) {
            throw new InvalidPageSizeException("Page size must be integer. Given: $value");
        }

        if (0 === $intVal) {
            throw new InvalidPageSizeException('Page size must not be 0.');
        }

        if ($intVal < -1) {
            throw new InvalidPageSizeException("Page size must be either > 0 or == -1. Given: $value");
        }

        return true;
    }

    public function normalize($value)
    {
        $pageSize = intval($value);
        if ($pageSize <= 0 && -1 !== $pageSize) {
            return current($this->validOptions);
        }

        return $pageSize;
    }

    /**
     * @return void
     */
    public function setValidOptions(array $validOptions)
    {
        $this->validOptions = $validOptions;
    }

    public function __construct(array $validOptions)
    {
        $this->setValidOptions($validOptions);
    }
}
