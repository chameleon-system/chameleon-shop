<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\Interfaces;

interface IAmazonReferenceIdList
{
    public function __construct($amazonOrderReferenceId, $type);

    /**
     * @param float  $value
     * @param string $transactionId the transaction id associated with the counter
     *
     * @return IAmazonReferenceId
     */
    public function getNew($value, $transactionId = null);

    /**
     * returns the last element in the list.
     *
     * @return IAmazonReferenceId
     */
    public function getLast();
}
