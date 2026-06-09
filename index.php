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
file_put_contents(
    __DIR__."/debug_payload.txt",
    "RAW:\n".$payload."\n\n",
    FILE_APPEND
);

/* ================= STORE PAYLOAD ================= */

file_put_contents(
    __DIR__."/webhook_log.txt",
    date("Y-m-d H:i:s")."\n".$payload."\n\n",
    FILE_APPEND
);

/* ================= DECODE JSON ================= */

$data = json_decode($payload, true);

$status    = strtoupper($data['status'] ?? 'UNKNOWN');
$reference = $data['customer_reference'] ?? 'N/A';
$number    = $data['msisdn'] ?? 'N/A';
$amount    = number_format($data['amount'] ?? 0);
$provider  = $data['provider'] ?? 'N/A';
$message1  = $data['message'] ?? '';
$time      = $data['completed_at'] ?? '';

/* ================= TELEGRAM MESSAGE ================= */

if ($status == "SUCCESS") {
    $title = "✅ PAYMENT STATUS: SUCCESS";
} else {
    $title = "❌ PAYMENT STATUS: FAILED";
}

$message  = $title."\n\n";
$message .= "📌 Reference: ".$reference."\n";
$message .= "📱 Number: ".$number."\n";
$message .= "💰 Amount: UGX ".$amount."\n";
$message .= "🏦 Provider: ".$provider."\n";
$message .= "📝 Message: ".$message1."\n";
$message .= "🕒 Time: ".$time;

/* ================= SEND TELEGRAM ================= */

file_get_contents(
    "https://api.telegram.org/bot{$botToken}/sendMessage?" .
    http_build_query([
        "chat_id" => $chatId,
        "text" => $message
    ])
);

/* ================= ONLY FORWARD SUCCESS ================= */

if ($status !== "SUCCESS") {

    file_put_contents(
        __DIR__."/failed_log.txt",
        date("Y-m-d H:i:s")."\n".$payload."\n\n",
        FILE_APPEND
    );

    echo "FAILED PAYMENT RECEIVED - NOT FORWARDED";
    exit;
}

<?php

$db_host = "sql309.byetcluster.com";
$db_name = "b5_42074304_shjeeeee";
$db_user = "b5_42074304";
$db_pass = "tiffheavenz";

$pdo = new PDO(
    "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
    $db_user,
    $db_pass,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]
);

/* RECEIVE JSON */
$payload = file_get_contents("php://input");
$data = json_decode($payload, true);

/* ONLY SUCCESS */
$status = strtoupper(trim($data['status'] ?? ''));

if ($status !== "SUCCESS") {
    exit("Ignored");
}

/* VALUES */
$reference      = trim($data['customer_reference'] ?? '');
$transaction_id = trim($data['internal_reference'] ?? '');
$message        = trim($data['message'] ?? '');

/* UPDATE DEPOSIT */
$stmt = $pdo->prepare("
    UPDATE deposits
    SET
        status='success',
        transaction_id=?,
        gateway_message=?,
        updated_at=NOW()
    WHERE reference=?
    AND status='pending'
");

$stmt->execute([
    $transaction_id,
    $message,
    $reference
]);

echo "OK";
?>
