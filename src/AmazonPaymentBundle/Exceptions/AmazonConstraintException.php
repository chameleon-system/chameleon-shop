<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\Exceptions;

class AmazonConstraintException extends \TPkgCmsException_Log
{
    /**
     * @var \OffAmazonPaymentsService_Model_OrderReferenceDetails
     */
    private $orderReferenceDetails = null;

    /**
     * @return \OffAmazonPaymentsService_Model_OrderReferenceDetails
     */
    public function getOrderReferenceDetails()
    {
        return $this->orderReferenceDetails;
    }

    /**
     * @param \OffAmazonPaymentsService_Model_OrderReferenceDetails $orderReferenceDetails
     *
     * @internal param \OffAmazonPaymentsService_Model_OrderReferenceDetails $constraints
     */
    public function setOrderReferenceDetails($orderReferenceDetails)
    {
        $this->orderReferenceDetails = $orderReferenceDetails;
    }
}
