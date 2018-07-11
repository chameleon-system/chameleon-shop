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
<?php


	$client = new OffAmazonPaymentsService_Client();
	$merchantValues = $client->getMerchantValues();
 ?>
 
<!DOCTYPE html>
<html>
    <head>
        <title>Address page</title>
        <script type="text/javascript" src=<?php print "'" . $merchantValues->getWidgetUrl() . "'"; ?> ></script>
    </head>
    <body>
        <div id="AmazonAddressWidget"></div>
 
        <p>Click <a id="WalletLink" href="">here</a> to go to the 
        wallet page once you have completed the signin</p>
        
        <script type='text/javascript' >
          var regexS = "[\\?&]" + "orderReferenceId" + "=([^&#]*)";
          var regex = new RegExp( regexS );
          var results = regex.exec( window.location.href );
          if (results != null) {
            var orderReferenceId = results[1].replace("?orderReferenceId");
            document.getElementById("WalletLink").href 
               = "wallet.php?orderReferenceId=" +  orderReferenceId;
          }
         
          new OffAmazonPayments.Widgets.AddressBook({
            sellerId: <?php print "\"" . $merchantValues->getMerchantId() . "\""; ?>,
            amazonOrderReferenceId: orderReferenceId,
            displayMode: 'Edit',
            design:{size: { width:'400', height:'228' } },
            onAddressSelect: function(orderReference) { 
              
            },
            onError: function(error) {
                alert(error.getErrorCode() + ": " + error.getErrorMessage());
            }
          }).bind("AmazonAddressWidget");
        </script>
    </body>
</html>
