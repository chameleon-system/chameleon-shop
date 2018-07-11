Chameleon System ShopRatingServiceBundle
========================================

Installation
------------

* To instruct the system to send rating request emails, activate the cronjob "Versand der Rating-Email-Aufforderungen".
  This can be omitted if EKomi is the only rating service in usage, as EKomi sends these emails automatically.
* If the cronjob is active, you need to implement custom logic to set the field
  'pkg_shop_rating_service_order_completely_shipped' in the order table after the order is shipped completely. Emails
  are sent only if this field is set to true.

