<?php

/*******************************************************************************
 *  Copyright 2013 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *
 *  You may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at:
 *  http://aws.amazon.com/apache2.0
 *  This file is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR
 *  CONDITIONS OF ANY KIND, either express or implied. See the License
 *  for the
 *  specific language governing permissions and limitations under the
 *  License.
 * *****************************************************************************
 */


/**
 * Get Order Reference Details  Sample
 */



/**
 * Verify that the order reference detail is in the expected state
 * 
 * @param OffAmazonPayments_Model_OrderReferenceDetails in an unverified state
 * @param string expected state of the object
 * 
 * @return void
 * @throws ErrorException if the state does not match the expected state
 */
function validateOrderReferenceIsInACorrectState($orderReferenceDetails, $expectedState)
{
	$state = $orderReferenceDetails->getOrderReferenceStatus()->getState();
	if (strcasecmp($state, $expectedState) != 0) {
		throw new ErrorException(
				"Error with order reference " .
				$orderReferenceDetails->getAmazonOrderReferenceId() . " - state is " .
				$state . " ,expected " . $expectedState . PHP_EOL
		);
	}
}

                                            
/**
  * Get Order Reference Details Action Sample
  
  * @param OffAmazonPaymentsService_Interface $service instance of OffAmazonPaymentsService_Interface
  * @param mixed $request OffAmazonPaymentsService_Model_GetOrderReferenceDetails or array of parameters
  */
function printGetOrderReferenceDetailsResponse($response) 
{

                print "Service Response" . PHP_EOL;
                print "=============================================================================" . PHP_EOL;

                print "        GetOrderReferenceDetailsResponse" . PHP_EOL;
                if ($response->isSetGetOrderReferenceDetailsResult()) { 
                    print "            GetOrderReferenceDetailsResult" . PHP_EOL;
                    $getOrderReferenceDetailsResult = $response->getGetOrderReferenceDetailsResult();
                    if ($getOrderReferenceDetailsResult->isSetOrderReferenceDetails()) { 
                        print "                OrderReferenceDetails" . PHP_EOL;
                        $orderReferenceDetails = $getOrderReferenceDetailsResult->getOrderReferenceDetails();
                        if ($orderReferenceDetails->isSetAmazonOrderReferenceId()) 
                        {
                            print "                    AmazonOrderReferenceId" . PHP_EOL;
                            print "                        " . $orderReferenceDetails->getAmazonOrderReferenceId() . PHP_EOL;
                        }
                        if ($orderReferenceDetails->isSetBuyer()) { 
                            print "                    Buyer" . PHP_EOL;
                            $buyer = $orderReferenceDetails->getBuyer();
                            if ($buyer->isSetName()) 
                            {
                                print "                        Name" . PHP_EOL;
                                print "                            " . $buyer->getName() . PHP_EOL;
                            }
                            if ($buyer->isSetEmail()) 
                            {
                                print "                        Email" . PHP_EOL;
                                print "                            " . $buyer->getEmail() . PHP_EOL;
                            }
                            if ($buyer->isSetPhone()) 
                            {
                                print "                        Phone" . PHP_EOL;
                                print "                            " . $buyer->getPhone() . PHP_EOL;
                            }
                        } 
                        if ($orderReferenceDetails->isSetOrderTotal()) { 
                            print "                    OrderTotal" . PHP_EOL;
                            $orderTotal = $orderReferenceDetails->getOrderTotal();
                            if ($orderTotal->isSetCurrencyCode()) 
                            {
                                print "                        CurrencyCode" . PHP_EOL;
                                print "                            " . $orderTotal->getCurrencyCode() . PHP_EOL;
                            }
                            if ($orderTotal->isSetAmount()) 
                            {
                                print "                        Amount" . PHP_EOL;
                                print "                            " . $orderTotal->getAmount() . PHP_EOL;
                            }
                        } 
                        if ($orderReferenceDetails->isSetSellerNote()) 
                        {
                            print "                    SellerNote" . PHP_EOL;
                            print "                        " . $orderReferenceDetails->getSellerNote() . PHP_EOL;
                        }
                        if ($orderReferenceDetails->isSetDestination()) { 
                            print "                    Destination" . PHP_EOL;
                            $destination = $orderReferenceDetails->getDestination();
                            if ($destination->isSetDestinationType()) 
                            {
                                print "                        DestinationType" . PHP_EOL;
                                print "                            " . $destination->getDestinationType() . PHP_EOL;
                            }
                            if ($destination->isSetPhysicalDestination()) { 
                                print "                        PhysicalDestination" . PHP_EOL;
                                $physicalDestination = $destination->getPhysicalDestination();
                                if ($physicalDestination->isSetName()) 
                                {
                                    print "                            Name" . PHP_EOL;
                                    print "                                " . $physicalDestination->getName() . PHP_EOL;
                                }
                                if ($physicalDestination->isSetAddressLine1()) 
                                {
                                    print "                            AddressLine1" . PHP_EOL;
                                    print "                                " . $physicalDestination->getAddressLine1() . PHP_EOL;
                                }
                                if ($physicalDestination->isSetAddressLine2()) 
                                {
                                    print "                            AddressLine2" . PHP_EOL;
                                    print "                                " . $physicalDestination->getAddressLine2() . PHP_EOL;
                                }
                                if ($physicalDestination->isSetAddressLine3()) 
                                {
                                    print "                            AddressLine3" . PHP_EOL;
                                    print "                                " . $physicalDestination->getAddressLine3() . PHP_EOL;
                                }
                                if ($physicalDestination->isSetCity()) 
                                {
                                    print "                            City" . PHP_EOL;
                                    print "                                " . $physicalDestination->getCity() . PHP_EOL;
                                }
                                if ($physicalDestination->isSetCounty()) 
                                {
                                    print "                            County" . PHP_EOL;
                                    print "                                " . $physicalDestination->getCounty() . PHP_EOL;
                                }
                                if ($physicalDestination->isSetDistrict()) 
                                {
                                    print "                            District" . PHP_EOL;
                                    print "                                " . $physicalDestination->getDistrict() . PHP_EOL;
                                }
                                if ($physicalDestination->isSetStateOrRegion()) 
                                {
                                    print "                            StateOrRegion" . PHP_EOL;
                                    print "                                " . $physicalDestination->getStateOrRegion() . PHP_EOL;
                                }
                                if ($physicalDestination->isSetPostalCode()) 
                                {
                                    print "                            PostalCode" . PHP_EOL;
                                    print "                                " . $physicalDestination->getPostalCode() . PHP_EOL;
                                }
                                if ($physicalDestination->isSetCountryCode()) 
                                {
                                    print "                            CountryCode" . PHP_EOL;
                                    print "                                " . $physicalDestination->getCountryCode() . PHP_EOL;
                                }
                                if ($physicalDestination->isSetPhone()) 
                                {
                                    print "                            Phone" . PHP_EOL;
                                    print "                                " . $physicalDestination->getPhone() . PHP_EOL;
                                }
                            } 
                        } 
                        if ($orderReferenceDetails->isSetReleaseEnvironment()) 
                        {
                            print "                    ReleaseEnvironment" . PHP_EOL;
                            print "                        " . $orderReferenceDetails->getReleaseEnvironment() . PHP_EOL;
                        }
                        if ($orderReferenceDetails->isSetSellerOrderAttributes()) { 
                            print "                    SellerOrderAttributes" . PHP_EOL;
                            $sellerOrderAttributes = $orderReferenceDetails->getSellerOrderAttributes();
                            if ($sellerOrderAttributes->isSetSellerOrderId()) 
                            {
                                print "                        SellerOrderId" . PHP_EOL;
                                print "                            " . $sellerOrderAttributes->getSellerOrderId() . PHP_EOL;
                            }
                            if ($sellerOrderAttributes->isSetStoreName()) 
                            {
                                print "                        StoreName" . PHP_EOL;
                                print "                            " . $sellerOrderAttributes->getStoreName() . PHP_EOL;
                            }
                            if ($sellerOrderAttributes->isSetOrderItemCategories()) { 
                                print "                        OrderItemCategories" . PHP_EOL;
                                $orderItemCategories = $sellerOrderAttributes->getOrderItemCategories();
                                $orderItemCategoryList  =  $orderItemCategories->getOrderItemCategory();
                                foreach ($orderItemCategoryList as $orderItemCategory) { 
                                    print "                            OrderItemCategory" . PHP_EOL;
                                    print "                                " . $orderItemCategory;
                                }	
                            } 
                            if ($sellerOrderAttributes->isSetCustomInformation()) 
                            {
                                print "                        CustomInformation" . PHP_EOL;
                                print "                            " . $sellerOrderAttributes->getCustomInformation() . PHP_EOL;
                            }
                        } 
                        if ($orderReferenceDetails->isSetOrderReferenceStatus()) { 
                            print "                    OrderReferenceStatus" . PHP_EOL;
                            $orderReferenceStatus = $orderReferenceDetails->getOrderReferenceStatus();
                            if ($orderReferenceStatus->isSetState()) 
                            {
                                print "                        State" . PHP_EOL;
                                print "                            " . $orderReferenceStatus->getState() . PHP_EOL;
                            }
                            if ($orderReferenceStatus->isSetLastUpdateTimestamp()) 
                            {
                                print "                        LastUpdateTimestamp" . PHP_EOL;
                                print "                            " . $orderReferenceStatus->getLastUpdateTimestamp() . PHP_EOL;
                            }
                            if ($orderReferenceStatus->isSetReasonCode()) 
                            {
                                print "                        ReasonCode" . PHP_EOL;
                                print "                            " . $orderReferenceStatus->getReasonCode() . PHP_EOL;
                            }
                            if ($orderReferenceStatus->isSetReasonDescription()) 
                            {
                                print "                        ReasonDescription" . PHP_EOL;
                                print "                            " . $orderReferenceStatus->getReasonDescription() . PHP_EOL;
                            }
                        } 
                        if ($orderReferenceDetails->isSetConstraints()) { 
                            print "                    Constraints" . PHP_EOL;
                            $constraints = $orderReferenceDetails->getConstraints();
                            $constraintList = $constraints->getConstraint();
                            foreach ($constraintList as $constraint) {
                                print "                        Constraint" . PHP_EOL;
                                if ($constraint->isSetConstraintID()) 
                                {
                                    print "                            ConstraintID" . PHP_EOL;
                                    print "                                " . $constraint->getConstraintID() . PHP_EOL;
                                }
                                if ($constraint->isSetDescription()) 
                                {
                                    print "                            Description" . PHP_EOL;
                                    print "                                " . $constraint->getDescription() . PHP_EOL;
                                }
                            }
                        } 
                        if ($orderReferenceDetails->isSetCreationTimestamp()) 
                        {
                            print "                    CreationTimestamp" . PHP_EOL;
                            print "                        " . $orderReferenceDetails->getCreationTimestamp() . PHP_EOL;
                        }
                        if ($orderReferenceDetails->isSetExpirationTimestamp()) 
                        {
                            print "                    ExpirationTimestamp" . PHP_EOL;
                            print "                        " . $orderReferenceDetails->getExpirationTimestamp() . PHP_EOL;
                        }
                    } 
                } 
                if ($response->isSetResponseMetadata()) { 
                    print "            ResponseMetadata" . PHP_EOL;
                    $responseMetadata = $response->getResponseMetadata();
                    if ($responseMetadata->isSetRequestId()) 
                    {
                        print "                RequestId" . PHP_EOL;
                        print "                    " . $responseMetadata->getRequestId() . PHP_EOL;
                    }
                } 

   	print "            ResponseHeaderMetadata: " . 
    $response->getResponseHeaderMetadata() . PHP_EOL;
        	
   	return $response;
}
?>                   