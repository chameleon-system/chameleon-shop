<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\objects\ArticleList;

use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\SortStringInterface;

class SortString implements SortStringInterface
{
    /**
     * @var string
     */
    private $sqlOrderByString;

    /**
     * @param string $sqlOrderByString
     */
    public function __construct($sqlOrderByString)
    {
        $this->sqlOrderByString = $sqlOrderByString;
    }

    public function getAsArray()
    {
        $sort = array();
        $parts = explode(',', $this->sqlOrderByString);
        foreach ($parts as $part) {
            $part = trim($part);
            if ('' === $part) {
                continue;
            }
            $tmpStr = str_replace("\n", ' ', strtoupper($part));
            $direction = 'ASC';
            if (' ASC' === substr($tmpStr, -4)) {
                $direction = 'ASC';
                $part = substr($part, 0, -4);
            } elseif (' DESC' === substr($tmpStr, -5)) {
                $direction = 'DESC';
                $part = substr($part, 0, -5);
            }

            $sort[$part] = $direction;
        }

        return $sort;
    }

    /**
     * @param string $sortDirection
     * @return string
     * @psalm-return 'ASC'|'DESC'
     */
    private function getSanitizedSortDirection($sortDirection)
    {
        $direction = strtoupper(trim($sortDirection));
        if (false === in_array($direction, array('ASC', 'DESC'))) {
            $direction = 'ASC';

            return $direction;
        }

        return $direction;
    }
}
