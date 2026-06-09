```php
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

$message = "🚨 WEBHOOK RECEIVED ON RENDER\n\n";

if (!empty($payload)) {
    $message .= $payload;
} else {
    $message .= "No payload received";
}

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
```
