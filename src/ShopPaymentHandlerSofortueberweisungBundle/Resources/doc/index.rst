Chameleon System ShopPaymentHandlerSofortueberweisungBundle
===========================================================

Setup
-----

Setup instructions are yet held in German and need to be both updated and translated.

1. sofortueberweisung-install-1.inc.php in private/extensions/updates/sofortueberweisung kopieren und CMS Update (via CMS Backend) ausführen
2. In Ihrem Kundenkonto auf sofortueberweisung (url im Moment: https://www.payment-network.com/sue_de/online-anbieterbereich/start)
   ein neues Projekt anlegen. Dabei folgende Werte übernehmen:

a) Shop-System: Anderes Shop-System
b) Anderes Shop-System: Chameleon Shop
c) Testmodus: Ja (zum testen, später auf Nein)
d) Erfolgslink: https://-USER_VARIABLE_0-?transaction=-TRANSACTION-&amount=-AMOUNT-&created=-TIMESTAMP-&currency_id=-CURRENCY_ID-&user_variable_3=-USER_VARIABLE_3-&user_variable_3_hash_pass=-USER_VARIABLE_3_HASH_PASS-
e) Abbruch-Link: https://-USER_VARIABLE_1-?transaction=-TRANSACTION-&amount=-AMOUNT-&created=-TIMESTAMP-&currency_id=-CURRENCY_ID-&user_variable_3=-USER_VARIABLE_3-&user_variable_3_hash_pass=-USER_VARIABLE_3_HASH_PASS-
f) unter "Benachrichtigungen" als HTTP(S)-URL: https://-USER_VARIABLE_2-

3. Nun müssen Sie noch ein Projektpasswort und ein Benachrichtigungspasswort hinterlegen. Dazu editieren Sie das gerade angelegt Projekt
   gehen auf den Reiter "Erweiterte Einstellungen" und dort auf "Passwörter und Hash-Algorithmus". Nachdem Sie die Passwörter hinterlegt haben,
   geben Sie unter "Input-Prüfung" als Hash-Algorithmus noch "SHA256" an.


Testen können Sie, indem Sie als BLZ beim Bezahlen 8 mal die 8 nehmen (88888888).

