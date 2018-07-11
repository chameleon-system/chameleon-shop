Chameleon System ShopPaymentIpnBundle
=====================================

This bundle manages instant payment notifications from payment providers.

Some important notes:

* the IPN Trigger expects an answer with header 200 and body containing "OK"
* the triggers are executed using the cronjobs - so there will be some delay caused by the cron-job schedule
* retrigger schedule is as follows (after 10 failed trigger calls, Chameleon will give up)

    * attempt Nr | deplay
    *          1 | 0
    *          2 | 5 Minutes
    *          3 | 15 Minutes
    *          4 | 1 hour
    *          5 | 4 hours
    *          6 | 4 hours
    *          7 | 8 hours
    *          8 | 24 hours
    *          9 | 24 hours
    *         10 | 24 hours