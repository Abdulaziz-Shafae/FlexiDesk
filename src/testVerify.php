<?php
require __DIR__ . '/vendor/autoload.php';

use Resend\Resend;

// يمكنك توليد كود عشوائي للاختبار
$code = rand(100000, 999999);

// إنشاء عميل Resend
$resend = Resend::client('re_eTvg2rQf_9nF72SBnbL9UECUSAXjCNz2K'); 

// إرسال الإيميل
$resend->emails->send([
  'from' => 'FlexiDesk <no-reply@flexidesk.com>',
  'to' => ['azoozshf1@gmail.com'],
  'subject' => 'Test Email',
  'html' => "<p>Your test code is: <strong>$code</strong></p>",
]);

echo "✅ Email sent to azoozshf1@gmail.com with code: $code";
