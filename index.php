<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

/* ================= TELEGRAM ================= */

$botToken = "8896732586:AAG2boPOp7mteDed11I2j7PYRn6L-Ln-3vQ";
$chatId   = "8940716704";

/* ================= DATABASE ================= */

$db_host = "sql309.byetcluster.com";
$db_name = "b5_42074304_shjeeeee";
$db_user = "b5_42074304";
$db_pass = "tiffheavenz";

try {

    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );

} catch (PDOException $e) {

    file_put_contents(
        __DIR__ . "/db_error.txt",
        date("Y-m-d H:i:s") . "\n" .
        $e->getMessage() . "\n\n",
        FILE_APPEND
    );

    die($e->getMessage());
}

/* ================= RECEIVE JSON ================= */

$payload = file_get_contents("php://input");

file_put_contents(
    __DIR__ . "/webhook_log.txt",
    date("Y-m-d H:i:s") . "\n" .
    $payload . "\n\n",
    FILE_APPEND
);

$data = json_decode($payload, true);

if (!$data) {
    exit("INVALID JSON");
}

/* ================= VALUES ================= */

$status         = strtoupper(trim($data['status'] ?? ''));
$reference      = trim($data['customer_reference'] ?? '');
$transaction_id = trim($data['internal_reference'] ?? '');
$message1       = trim($data['message'] ?? '');
$number         = trim($data['msisdn'] ?? '');
$amount         = number_format($data['amount'] ?? 0);
$provider       = trim($data['provider'] ?? '');
$time           = trim($data['completed_at'] ?? '');

/* ================= TELEGRAM ================= */

$title = ($status == "SUCCESS")
    ? "✅ PAYMENT STATUS: SUCCESS"
    : "❌ PAYMENT STATUS: FAILED";

$message  = $title . "\n\n";
$message .= "📌 Reference: " . $reference . "\n";
$message .= "📱 Number: " . $number . "\n";
$message .= "💰 Amount: UGX " . $amount . "\n";
$message .= "🏦 Provider: " . $provider . "\n";
$message .= "📝 Message: " . $message1 . "\n";
$message .= "🕒 Time: " . $time;

@file_get_contents(
    "https://api.telegram.org/bot{$botToken}/sendMessage?" .
    http_build_query([
        "chat_id" => $chatId,
        "text" => $message
    ])
);

/* ================= IGNORE FAILED ================= */

if ($status !== "SUCCESS") {
    exit("IGNORED");
}

/* ================= UPDATE DEPOSIT ================= */

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
    $message1,
    $reference
]);

file_put_contents(
    __DIR__ . "/update_log.txt",
    date("Y-m-d H:i:s") .
    "\nReference: " . $reference .
    "\nRows Updated: " . $stmt->rowCount() .
    "\n\n",
    FILE_APPEND
);

echo "OK";

?>
