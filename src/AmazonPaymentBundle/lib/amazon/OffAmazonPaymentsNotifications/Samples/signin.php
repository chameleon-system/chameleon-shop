<!-- 
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
 -->
<!DOCTYPE html>
<?php 


	$client = new OffAmazonPaymentsService_Client();
	$merchantValues = $client->getMerchantValues();
 ?>

<html>
    <head>
        <title>Login page</title>
        <script type="text/javascript" src=<?php print "'" . $merchantValues->getWidgetUrl() . "'"; ?> ></script>
    </head>
    <body>
        <script type='text/javascript'>
            new OffAmazonPayments.Widgets.Button({
                sellerId: <?php print "\"" . $merchantValues->getMerchantId() . "\""; ?> , 
                onSignIn: function(orderReference) {
                    window.location = 'address.php?orderReferenceId=' +
                        orderReference.getAmazonOrderReferenceId();
            },
            onError: function(error) {
                alert(error.getErrorCode() + ": " + error.getErrorMessage());
            }
            }).bind("AmazonPayButton");
        </script>

        <div id="AmazonPayButton"></div>

        <p>Sign in with a test buyer account to redirect to the address
        widget page</p>
    </body>
</html>
