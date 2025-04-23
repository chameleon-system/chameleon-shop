# Chameleon System ShopPaymentHandlerSofortueberweisungBundle

## Handbook

- [Handbuch_Eigenintegration_sofortueberweisung.de](./Resources/doc/Handbuch_Eigenintegration_sofortueberweisung.de_v.2.0.pdf)


## Setup

Setup instructions are yet held in German and need to be both updated and translated.

1. Copy sofortueberweisung-install-1.inc.php to private/extensions/updates/sofortueberweisung and execute CMS Update (via CMS Backend).
2. In your customer account on sofortueberweisung (url at the moment: [https://www.payment-network.com/sue_de/online-anbieterbereich/start](https://www.payment-network.com/sue_de/online-anbieterbereich/start)), create a new project with the following values:

   a) Shop-System: Anderes Shop-System
   b) Anderes Shop-System: Chameleon Shop
   c) Testmodus: Ja (to test, later set to Nein)
   d) Erfolgslink: [https://-USER_VARIABLE_0-?transaction=-TRANSACTION-&amount=-AMOUNT-&created=-TIMESTAMP-&currency_id=-CURRENCY_ID-&user_variable_3=-USER_VARIABLE_3-&user_variable_3_hash_pass=-USER_VARIABLE_3_HASH_PASS-](https://-USER_VARIABLE_0-?transaction=-TRANSACTION-&amount=-AMOUNT-&created=-TIMESTAMP-&currency_id=-CURRENCY_ID-&user_variable_3=-USER_VARIABLE_3-&user_variable_3_hash_pass=-USER_VARIABLE_3_HASH_PASS-)
   e) Abbruch-Link: [https://-USER_VARIABLE_1-?transaction=-TRANSACTION-&amount=-AMOUNT-&created=-TIMESTAMP-&currency_id=-CURRENCY_ID-&user_variable_3=-USER_VARIABLE_3-&user_variable_3_hash_pass=-USER_VARIABLE_3_HASH_PASS-](https://-USER_VARIABLE_1-?transaction=-TRANSACTION-&amount=-AMOUNT-&created=-TIMESTAMP-&currency_id=-CURRENCY_ID-&user_variable_3=-USER_VARIABLE_3-&user_variable_3_hash_pass=-USER_VARIABLE_3_HASH_PASS-)
   f) under "Benachrichtigungen" as HTTP(S)-URL: [https://-USER_VARIABLE_2-](https://-USER_VARIABLE_2-)

3. Now you need to set a project password and a notification password. To do this, edit the project you just created, go to the "Erweiterte Einstellungen" tab, and then to "Passwörter und Hash-Algorithmus". After setting the passwords, specify "SHA256" as the Hash Algorithm under "Input-Prüfung".

You can test by using 8 eight times as the BLZ when paying (88888888).