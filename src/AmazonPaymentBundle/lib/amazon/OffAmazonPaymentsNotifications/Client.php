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
 * Implementation of the OffAmazonPaymentsNotifications
 * library
 * 
 */
class OffAmazonPaymentsNotifications_Client 
    implements OffAmazonPaymentsNotifications_Interface
{
    /**
     * Store an instance of the sns message validator
     * object
     *
     * @var SnsMessageValidator
     */
    private $_snsMessageValidator = null;
    
    /**
     * Create an instance of the client class
     * 
     * @return void
     */
    public function __construct()
    {
        $this->_snsMessageValidator 
            = new SnsMessageValidator(new OpenSslVerifySignature());  
    }
    
    /**
     * Converts a http POST body and headers into
     * a notification object
     * 
     * @param array  $headers post request headers
     * @param string $body    post request body, should be json
     * 
     * @throws OffAmazonPaymentsNotifications_InvalidMessageException
     * 
     * @return OffAmazonPaymentsNotifications_Notification
     */
    public function parseRawMessage($headers, $body)
    {
        // Is this json, is this
        // an sns message, do we have the fields we require
        $snsMessage = SnsMessageParser::parseNotification($headers, $body);
        
        // security validation - check that this message is
        // from amazon and that it has been signed correctly
        $this->_snsMessageValidator->validateMessage($snsMessage);
        
        // Convert to object - convert from basic class to object
        $ipnMessage = IpnNotificationParser::parseSnsMessage($snsMessage);
        return XmlNotificationParser::parseIpnMessage($ipnMessage);
    }
}
?>