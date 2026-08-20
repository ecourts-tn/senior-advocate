<?php

require __DIR__ . '/vendor/autoload.php';

use Config\Services;

$email = Services::email();

$email->setFrom('syanltmhc.ecourts@tn.gov.in', 'Senior Advocate System');
$email->setTo('deenadayalan.mhc@gmail.com');

$email->setSubject('CI4 Email Test');
$email->setMessage('
    <h3>Email Test Successful</h3>
    <p>This is a test email sent from the Senior Advocate CodeIgniter 4 application.</p>
');

if ($email->send()) {
    echo "SUCCESS: Email sent successfully.";
} else {
    echo "ERROR: Email could not be sent.<br><br>";
    echo nl2br($email->printDebugger(['headers', 'subject', 'body']));
}