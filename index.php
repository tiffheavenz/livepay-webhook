<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

/* ================= TELEGRAM ================= */

$botToken = "8896732586:AAG2boPOp7mteDed11I2j7PYRn6L-Ln-3vQ";
$chatId   = "8940716704";

/* ================= SUPABASE POOLER CONNECTION ================= */
/* IMPORTANT: Replace YOUR_REAL_DB_PASSWORD with Supabase DB password */

try {

    $pdo = new PDO(
        "pgsql:host=aws-0-eu-west-3.pooler.supabase.com;port=5432;dbname=postgres",
        "postgres.lxsddkbtbynekazmdsbh",
        "@Shjeeee2024",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );

} catch (PDOException $e) {

    // Send DB error to Telegram
    file_get_contents(
        "https://api.telegram.org/bot{$botToken}/sendMessage?" .
        http_build_query([
            "chat_id" => $chatId,
            "text" => "❌ DB CONNECTION FAILED\n" . $e->getMessage()
        ])
    );

    exit("DB CONNECTION FAILED");
}

/* ================= RECEIVE PAYLOAD ================= */

$payload = file_get_contents("php://input");

if (empty(trim($payload))) {
    exit("No payload received");
}

/* ================= DECODE JSON ================= */

$data = json_decode($payload, true);

if (json_last_error() !== JSON_ERROR_NONE) {

    file_get_contents(
        "https://api.telegram.org/bot{$botToken}/sendMessage?" .
        http_build_query([
            "chat_id" => $chatId,
            "text" => "❌ INVALID JSON RECEIVED\n\n" . $payload
        ])
    );

    exit("Invalid JSON");
}

/* ================= VALUES ================= */

$status    = strtoupper(trim($data['status'] ?? 'UNKNOWN'));
$reference = trim($data['customer_reference'] ?? 'N/A');
$number    = trim($data['msisdn'] ?? 'N/A');
$amount    = (float)($data['amount'] ?? 0);
$provider  = trim($data['provider'] ?? 'N/A');
$message1  = trim($data['message'] ?? '');
$time      = trim($data['completed_at'] ?? date("Y-m-d H:i:s"));

/* ================= TELEGRAM MESSAGE ================= */

$title = ($status === "SUCCESS")
    ? "✅ PAYMENT STATUS: SUCCESS"
    : "❌ PAYMENT STATUS: FAILED";

$message  = $title . "\n\n";
$message .= "📌 Reference: " . $reference . "\n";
$message .= "📱 Number: " . $number . "\n";
$message .= "💰 Amount: UGX " . number_format($amount) . "\n";
$message .= "🏦 Provider: " . $provider . "\n";
$message .= "📝 Message: " . $message1 . "\n";
$message .= "🕒 Time: " . $time;

/* SEND TO TELEGRAM */

file_get_contents(
    "https://api.telegram.org/bot{$botToken}/sendMessage?" .
    http_build_query([
        "chat_id" => $chatId,
        "text" => $message
    ])
);

/* ================= IGNORE FAILED PAYMENTS ================= */

if ($status !== "SUCCESS") {
    echo "FAILED PAYMENT - NOT STORED";
    exit;
}

/* ================= STORE SUCCESS TRANSACTION ================= */

$stmt = $pdo->prepare("
    INSERT INTO transactions
    (reference, status, amount, msisdn, provider, message, completed_at)
    VALUES
    (:reference, :status, :amount, :msisdn, :provider, :message, :completed_at)
    ON CONFLICT (reference)
    DO UPDATE SET
        status = EXCLUDED.status,
        amount = EXCLUDED.amount,
        msisdn = EXCLUDED.msisdn,
        provider = EXCLUDED.provider,
        message = EXCLUDED.message,
        completed_at = EXCLUDED.completed_at
");

$stmt->execute([
    ":reference" => $reference,
    ":status" => $status,
    ":amount" => $amount,
    ":msisdn" => $number,
    ":provider" => $provider,
    ":message" => $message1,
    ":completed_at" => $time
]);

echo "SUCCESS PAYMENT STORED";

?>
