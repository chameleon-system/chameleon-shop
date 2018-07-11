#!/bin/bash

echo "remove autoloader"
rm ./../lib/amazon/OffAmazonPayments/.autoloader.php

echo "remove require"
find ./../lib/ -name "*.php" | xargs sed -i 's,^.*require_once.*$,,'

echo "make sure to search for include_once (you should find matching entries in: OffAmazonPayments_Model and OffAmazonPaymentsService_MerchantValues)"