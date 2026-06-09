<?php

/* ================= TELEGRAM ================= */

$botToken = "8896732586:AAG2boPOp7mteDed11I2j7PYRn6L-Ln-3vQ";
$chatId   = "8940716704";

/* ================= SECRET ================= */

$secret = "MY_SUPER_SECRET_KEY";

/* ================= WEBSITE WEBHOOK ================= */

$websiteWebhook = "https://shjeeee.byethost5.com/Shjeeee/webhook.php";

/* ================= RECEIVE WEBHOOK ================= */

$payload = file_get_contents("php://input");

/* ================= LOG RAW PAYLOAD ================= */

file_put_contents(
    __DIR__."/webhook_log.txt",
    date("Y-m-d H:i:s")."\n".$payload."\n\n",
    FILE_APPEND
);

$data = json_decode($payload, true);

/* ================= TELEGRAM ================= */

$message  = "💰 LivePay Webhook Received\n\n";
$message .= "Reference: ".($data['customer_reference'] ?? 'N/A')."\n";
$message .= "Internal Ref: ".($data['internal_reference'] ?? 'N/A')."\n";
$message .= "Phone: ".($data['msisdn'] ?? 'N/A')."\n";
$message .= "Amount: ".($data['amount'] ?? 'N/A')."\n";
$message .= "Status: ".($data['status'] ?? 'N/A');

@file_get_contents(
    "https://api.telegram.org/bot{$botToken}/sendMessage?" .
    http_build_query([
        "chat_id" => $chatId,
        "text" => $message
    ])
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
        __DIR__."/webhook_error.log",
        date("Y-m-d H:i:s")."\n".
        curl_error($ch)."\n\n",
        FILE_APPEND
    );
}

curl_close($ch);

echo "OK";
```
<?php

// =======================
// TELEGRAM SETTINGS
// =======================
$botToken = "8896732586:AAG2boPOp7mteDed11I2j7PYRn6L-Ln-3vQ";
$chatId   = "8940716704";

// =======================
// YOUR WEBSITE WEBHOOK URL
// =======================
$websiteWebhook = "https://https://shjeeee.byethost5.com/Shjeeee/webhook.php";

// =======================
// RECEIVE LIVEPAY WEBHOOK
// =======================
$payload = file_get_contents("php://input");

// Save webhook for debugging
file_put_contents(
    "webhook_log.txt",
    date("Y-m-d H:i:s") . "\n" .
    $payload . "\n\n",
    FILE_APPEND
);

// Decode JSON
$data = json_decode($payload, true);

// =======================
// TELEGRAM NOTIFICATION
// =======================
$message = "💰 LivePay Webhook Received\n\n";

$message .= "Transaction ID: " . ($data['transaction_id'] ?? 'N/A') . "\n";
$message .= "Amount: " . ($data['amount'] ?? 'N/A') . "\n";
$message .= "Phone: " . ($data['phone'] ?? 'N/A') . "\n";
$message .= "Status: " . ($data['status'] ?? 'N/A');

// Send Telegram message
file_get_contents(
    "https://api.telegram.org/bot{$botToken}/sendMessage?" .
    http_build_query([
        "chat_id" => $chatId,
        "text" => $message
    ])
);

// =======================
// FORWARD TO YOUR WEBSITE
// =======================
$ch = curl_init($websiteWebhook);

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    file_put_contents(
        "webhook_error.log",
        date("Y-m-d H:i:s") . "\n" .
        curl_error($ch) . "\n\n",
        FILE_APPEND
    );
}

curl_close($ch);

// Respond to LivePay
echo "OK";

?>
