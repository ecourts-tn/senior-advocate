<?php

/**
 * One-off SMTP test using the same CodeIgniter 4 Email class as the portal.
 *
 * Usage (from project root):
 *   php test-smtp.php you@example.com
 */

use CodeIgniter\Boot;
use Config\Paths;
use Config\Services;

// if (PHP_SAPI !== 'cli') {
//     exit("Run from command line: php test-smtp.php you@example.com\n");
// }

$minPhpVersion = '8.1.2';
if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
    exit("Need PHP {$minPhpVersion}+. Current: " . PHP_VERSION . "\n");
}

define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
chdir(FCPATH);

require FCPATH . '../app/Config/Paths.php';
$paths = new Paths();
require $paths->systemDirectory . '/Boot.php';
Boot::bootConsole($paths);

$to = $argv[1] ?? '';
if ($to === '' || ! filter_var($to, FILTER_VALIDATE_EMAIL)) {
    echo "Usage: php test-smtp.php you@example.com\n";
    exit(1);
}

$cfg = model(\App\Models\SystemSettingModel::class)->getGroup('email');

echo "PHP        : " . PHP_VERSION . "\n";
echo "protocol   : " . ($cfg['protocol'] ?? '') . "\n";
echo "enabled    : " . ($cfg['enabled'] ?? '') . "\n";
echo "from       : " . ($cfg['from_email'] ?? '') . "\n";
echo "smtp_host  : " . ($cfg['smtp_host'] ?? '') . "\n";
echo "smtp_user  : " . ($cfg['smtp_user'] ?? '') . "\n";
echo "smtp_port  : " . ($cfg['smtp_port'] ?? '') . "\n";
echo "smtp_crypto: " . ($cfg['smtp_crypto'] ?? '') . "\n";
echo "to         : {$to}\n\n";

$email = Services::email();
$email->initialize([
    'protocol'     => 'smtp',
    'SMTPHost'     => (string) ($cfg['smtp_host'] ?? ''),
    'SMTPUser'     => (string) ($cfg['smtp_user'] ?? ''),
    'SMTPPass'     => (string) ($cfg['smtp_pass'] ?? ''),
    'SMTPPort'     => (int) ($cfg['smtp_port'] ?? 25),
    'SMTPCrypto'   => (string) ($cfg['smtp_crypto'] ?? ''),
    'SMTPTimeout'  => 30,
    'mailType'     => 'html',
    'charset'      => 'UTF-8',
    'wordWrap'     => true,
]);
$email->setFrom((string) ($cfg['from_email'] ?? ''), (string) ($cfg['from_name'] ?? 'SSA Portal'));
$email->setTo($to);
$email->setSubject('SSA CI4 SMTP test');
$email->setMessage('<p>Test email from CI4 on ' . date('Y-m-d H:i:s') . '</p>');

$ok = $email->send(false);

echo $ok ? "RESULT: SMTP OK\n\n" : "RESULT: SMTP FAILED\n\n";
echo $email->printDebugger(['headers', 'subject', 'body']);
echo "\n";

exit($ok ? 0 : 1);
