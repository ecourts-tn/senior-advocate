<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Config\Services;

$email = Services::email();

$email->setFrom('your-sender@example.com', 'Senior Advocate System');
$email->setTo('your-recipient@example.com');

$email->setSubject('SMTP Test - Senior Advocate');
$email->setMessage('
    <h3>SMTP Test</h3>
    <p>This is a test email from the Senior Advocate CodeIgniter 4 application.</p>
    <p>If you received this email, SMTP is working correctly.</p>
');

if ($email->send()) {
    echo '<h3 style="color:green;">SUCCESS: Email sent successfully.</h3>';
} else {
    echo '<h3 style="color:red;">ERROR: Email could not be sent.</h3>';
    echo '<pre>';
    print_r($email->printDebugger(['headers', 'subject', 'body']));
    echo '</pre>';
}