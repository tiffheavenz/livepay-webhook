<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

/* ================= TELEGRAM ================= */

$botToken = "8896732586:AAG2boPOp7mteDed11I2j7PYRn6L-Ln-3vQ";
$chatId   = "8940716704";

/* ================= SECRET ================= */

$secret = "MY_SUPER_SECRET_KEY";

/* ================= WEBSITE WEBHOOK ================= */

$websiteWebhook = "https://shjeeee.byethost5.com/Shjeeee/webhook.php";

/* ================= RECEIVE PAYLOAD ================= */

$payload = file_get_contents("php://input");

/* ================= SAVE PAYLOAD ================= */

file_put_contents(
    __DIR__ . "/webhook_log.txt",
    date("Y-m-d H:i:s") . "\n" .
    $payload . "\n\n",
    FILE_APPEND
);

/* ================= SEND TELEGRAM IMMEDIATELY ================= */

```php
/* ================= FORMAT TELEGRAM MESSAGE ================= */

$data = json_decode($payload, true);

$status    = strtoupper($data['status'] ?? 'UNKNOWN');
$reference = $data['customer_reference'] ?? 'N/A';
$number    = $data['msisdn'] ?? 'N/A';
$amount    = number_format($data['amount'] ?? 0);
$provider  = $data['provider'] ?? 'N/A';
$message1  = $data['message'] ?? '';
$time      = $data['completed_at'] ?? '';

if ($status == "SUCCESS") {
    $title = "✅ PAYMENT STATUS: SUCCESS";
} else {
    $title = "❌ PAYMENT STATUS: FAILED";
}

$message = $title . "\n\n";
$message .= "📌 Reference: " . $reference . "\n";
$message .= "📱 Number: " . $number . "\n";
$message .= "💰 Amount: UGX " . $amount . "\n";
$message .= "🏦 Provider: " . $provider . "\n";
$message .= "📝 Message: " . $message1 . "\n";
$message .= "🕒 Time: " . $time;
```


$telegramUrl = "https://api.telegram.org/bot{$botToken}/sendMessage";

$telegramResponse = @file_get_contents(
    $telegramUrl . "?" . http_build_query([
        "chat_id" => $chatId,
        "text" => $message
    ])
);

/* ================= LOG TELEGRAM RESPONSE ================= */

file_put_contents(
    __DIR__ . "/telegram_log.txt",
    date("Y-m-d H:i:s") . "\n" .
    $telegramResponse . "\n\n",
    FILE_APPEND
);

/* ================= FORWARD TO WEBSITE ================= */

$ch = curl_init($websiteWebhook);

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "X-Secret: ".$secret
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {

    file_put_contents(
        __DIR__ . "/webhook_error.log",
        date("Y-m-d H:i:s") . "\n" .
        curl_error($ch) . "\n\n",
        FILE_APPEND
    );
}

curl_close($ch);

/* ================= RETURN TO LIVEPAY ================= */

echo "OK";

?>
