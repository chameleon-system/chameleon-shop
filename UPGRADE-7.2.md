UPGRADE FROM 7.1 to 7.2
=======================

# Deprecated Bundles

## AmazonPaymentBundle

The AmazonPaymentBundle was not usable anymore, because Amazon deprecated the API and old SDK. It was removed.
Check your AppKernel and remove the bundle if activated.
Use the AmazonPayBundle instead as a replacement (not publicly available).

