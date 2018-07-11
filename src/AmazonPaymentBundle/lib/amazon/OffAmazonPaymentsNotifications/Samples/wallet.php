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
		<title>Wallet page</title>
		<script type="text/javascript" src=<?php print "'" . $merchantValues->getWidgetUrl() . "'"; ?> ></script>
	</head>
	<body>
		<div id="AmazonWalletWidget"></div>
		<script type='text/javascript' >
		  var regexS = "[\\?&]" + "orderReferenceId" + "=([^&#]*)";
		  var regex = new RegExp( regexS );
		  var results = regex.exec( window.location.href );
		  if (results != null) {
		    var orderReferenceId = results[1].replace("?orderReferenceId");
		  }
		 
		  new OffAmazonPayments.Widgets.Wallet({
		    sellerId: <?php print "\"" . $merchantValues->getMerchantId() . "\""; ?>,
		    amazonOrderReferenceId: orderReferenceId,
		    displayMode: 'Edit',
		    design:{size: { width:'400', height:'228' } },
		    onPaymentSelect: function(orderReference) {
		      
		    },
		    onError: function(error) {
		    	alert(error.getErrorCode() + ": " + error.getErrorMessage());
		    }
		  }).bind("AmazonWalletWidget");
		</script>
	</body>
</html>