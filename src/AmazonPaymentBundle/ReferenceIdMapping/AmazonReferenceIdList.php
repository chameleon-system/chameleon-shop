<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping;

use ChameleonSystem\AmazonPaymentBundle\Interfaces\IAmazonReferenceId;
use ChameleonSystem\AmazonPaymentBundle\Interfaces\IAmazonReferenceIdList;
use Traversable;

class AmazonReferenceIdList implements IAmazonReferenceIdList, \IteratorAggregate, \Countable
{
    private $mappingList = array();
    /**
     * @var string
     */
    private $amazonOrderReferenceId;
    /**
     * @var
     */
    private $type;

    public function __construct($amazonOrderReferenceId, $type)
    {
        $this->type = $type;
        $this->amazonOrderReferenceId = $amazonOrderReferenceId;
    }

    /**
     * {@inheritdoc}
     */
    public function getNew($value, $transactionId = null)
    {
        $item = new AmazonReferenceId($this->type, $this->generateLocalId(), $value, $transactionId);
        $this->mappingList[] = $item;

        return $item;
    }

    private function generateLocalId()
    {
        $parts = array();
        $parts[] = $this->amazonOrderReferenceId;
        switch ($this->type) {
            case IAmazonReferenceId::TYPE_AUTHORIZE:
                $parts[] = '-A';
                break;
            case IAmazonReferenceId::TYPE_CAPTURE:
                $parts[] = '-C';
                break;
            case IAmazonReferenceId::TYPE_REFUND:
                $parts[] = '-R';
                break;
        }
        $maxLength = 32 - 2 - mb_strlen($this->amazonOrderReferenceId);
        $parts[] = sprintf("%0{$maxLength}u", count($this->mappingList));

        return implode('', $parts);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator.
     *
     * @see http://php.net/manual/en/iteratoraggregate.getiterator.php
     *
     * @return Traversable    An instance of an object implementing <b>Iterator</b> or
     *                        <b>Traversable</b>
     * @return \ArrayIterator
     */
    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->mappingList);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object.
     *
     * @see http://php.net/manual/en/countable.count.php
     *
     * @return int the custom count as an integer.
     *             </p>
     *             <p>
     *             The return value is cast to an integer
     */
    public function count(): int
    {
        return count($this->mappingList);
    }

    public function addItem(IAmazonReferenceId $item)
    {
        if ($item->getType() !== $this->type) {
            throw new \InvalidArgumentException("expecting item of type {$this->type}, but got type ".$item->getType(
                ));
        }

        // make sure item does not exists
        $this->mappingList[] = $item;
    }

    /**
     * returns the last element in the list.
     *
     * @return IAmazonReferenceId
     */
    public function getLast()
    {
        return $this->mappingList[count($this->mappingList) - 1];
    }
}
