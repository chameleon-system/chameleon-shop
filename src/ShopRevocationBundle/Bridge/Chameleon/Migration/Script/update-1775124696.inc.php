<h1>Build #1775124696</h1>
<h2>Date: 2026-04-02</h2>
<div class="changelog">
    - #69674: Add revocation mail profiles
</div>
<?php

$data = TCMSLogChange::createMigrationQueryData('data_mail_profile', 'de')
  ->setFields([
      'name' => '',
      'id' => '41502190-84ec-27b9-be90-072fdb9e3b01',
  ])
;
TCMSLogChange::insert(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('data_mail_profile', 'de')
  ->setFields([
      'name' => '',
      'id' => '7a40835a-51a7-40d4-b01c-cdf4dc2c4d5d',
  ])
;
TCMSLogChange::insert(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('data_mail_profile', 'de')
  ->setFields([
      'idcode' => 'revocation_customer', // prev.: ''
      'name' => 'Widerrufbenachrichtigung Kunde', // prev.: ''
      'subject' => 'Widerruf-Bestätigung für Bestellung: #[{ordernumber}]', // prev.: ''
      'body' => '<div style="font-size: 12px">
  <p><strong>Sehr geehrte(r) [{customerName}],</strong></p>
  <p>wir haben Ihre Widerrufsanfrage für die Bestellung #[{ordernumber}] vom [{orderDate}] erhalten.</p>
  <p>Datum des Eingangs: [{revocationDate}]<br />Uhrzeit des Eingangs: [{revocationTime}]</p>
  <p>Den Inhalt Ihrer übermittelten Widerrufsanfrage finden Sie hier:</p>
  <p>Name: [{customerName}]<br />E-Mail: [{customerMail}]<br />Bestellnummer: [{ordernumber}]<br />Nachricht: [{customerText}]</p>
</div>', // prev.: ''
      'body_text' => 'Sehr geehrte(r) [{customerName}]
wir haben Ihre Widerrufsanfrage für die Bestellung #[{ordernumber}] vom [{orderDate}] erhalten.
Datum des Eingangs: [{revocationDate}]
Uhrzeit des Eingangs: [{revocationTime}]

Den Inhalt Ihrer übermittelten Widerrufsanfrage finden Sie hier:
Name: [{customerName}]
E-Mail: [{customerMail}]
Bestellnummer: [{ordernumber}]
Nachricht: [{customerText}]', // prev.: ''
      'template' => 'standard', // prev.: ''
      'template_text' => 'standard.txt', // prev.: ''
  ])
  ->setWhereEquals([
      'id' => '41502190-84ec-27b9-be90-072fdb9e3b01',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('data_mail_profile', 'en')
        ->setFields([
                'subject' => 'Revocation confirmation for order: #[{ordernumber}]', // prev.: ''
                'body' => '<div style="font-size: 12px">
    <p><strong>Dear [{customerName}],</strong></p>
    <p>We have received your revocation request for order #[{ordernumber}] dated [{orderDate}].</p>
    <p>Date of receipt: [{revocationDate}]<br />Time of receipt: [{revocationTime}]</p>
    <p>You can find the content of your submitted revocation request here:</p>
    <p>Name: [{customerName}]<br />Email: [{customerMail}]<br />Order number: [{ordernumber}]<br />Message:
  [{customerText}]</p>
  </div>', // prev.: ''
                'body_text' => 'Dear [{customerName}]
  We have received your revocation request for order #[{ordernumber}] dated [{orderDate}].
  Date of receipt: [{revocationDate}]
  Time of receipt: [{revocationTime}]

  You can find the content of your submitted revocation request here:
  Name: [{customerName}]
  Email: [{customerMail}]
  Order number: [{ordernumber}]
  Message: [{customerText}]', // prev.: ''
                'template' => 'standard', // prev.: ''
                'template_text' => 'standard.txt', // prev.: ''
        ])
        ->setWhereEquals([
                'id' => '41502190-84ec-27b9-be90-072fdb9e3b01',
        ])
;
TCMSLogChange::update(__LINE__, $data);


$data = TCMSLogChange::createMigrationQueryData('data_mail_profile', 'de')
  ->setFields([
      'idcode' => 'revocation_shop_owner',
      'name' => 'Widerrufbenachrichtigung Shopbetreiber',
      'subject' => 'Neuer Widerruf für Bestellung: #[{ordernumber}]',
      'body' => '<div style="font-size: 12px">
  <p>Es wurde ein neuer Widerruf über das Widerrufsformular übermittelt.</p>
  <p>Bestellung #[{ordernumber}] vom [{orderDate}]</p>
  <p>Datum des Eingangs: [{revocationDate}]<br />Uhrzeit des Eingangs: [{revocationTime}]</p>
  <p>Name: [{customerName}]<br />E-Mail: [{customerMail}]<br />Bestellnummer: [{ordernumber}]<br />Nachricht: [{customerText}]</p>
</div>',
      'body_text' => 'Es wurde ein neuer Widerruf über das Widerrufsformular übermittelt.
Bestellung #[{ordernumber}] vom [{orderDate}]
Datum des Eingangs: [{revocationDate}]
Uhrzeit des Eingangs: [{revocationTime}]

Name: [{customerName}]
E-Mail: [{customerMail}]
Bestellnummer: [{ordernumber}]
Nachricht: [{customerText}]',
      'template' => 'standard',
      'template_text' => 'standard.txt',
  ])
  ->setWhereEquals([
      'id' => '7a40835a-51a7-40d4-b01c-cdf4dc2c4d5d',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('data_mail_profile', 'en')
        ->setFields([
                'idcode' => 'revocation_shop_owner',
                'name' => 'Revocation notification shop owner',
                'subject' => 'New revocation for order: #[{ordernumber}]',
                'body' => '<div style="font-size: 12px">
    <p>A new revocation has been submitted via the revocation form.</p>
    <p>Order #[{ordernumber}] dated [{orderDate}]</p>
    <p>Date of receipt: [{revocationDate}]<br />Time of receipt: [{revocationTime}]</p>
    <p>Name: [{customerName}]<br />Email: [{customerMail}]<br />Order number: [{ordernumber}]<br />Message:
  [{customerText}]</p>
  </div>',
                'body_text' => 'A new revocation has been submitted via the revocation form.
  Order #[{ordernumber}] dated [{orderDate}]
  Date of receipt: [{revocationDate}]
  Time of receipt: [{revocationTime}]

  Name: [{customerName}]
  Email: [{customerMail}]
  Order number: [{ordernumber}]
  Message: [{customerText}]',
                'template' => 'standard',
                'template_text' => 'standard.txt',
        ])
        ->setWhereEquals([
                'id' => '7a40835a-51a7-40d4-b01c-cdf4dc2c4d5d',
        ])
;
TCMSLogChange::update(__LINE__, $data);
