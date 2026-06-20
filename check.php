<?php

header('Content-Type: application/json');

$ref = $_GET['ref'] ?? '';

$file = __DIR__ . "/transactions.json";

if (!$ref || !file_exists($file)) {

    echo json_encode([
        "found" => false
    ]);

    exit;
}

$transactions = json_decode(file_get_contents($file), true);

if (isset($transactions[$ref])) {

    echo json_encode([
        "found" => true,
        "status" => $transactions[$ref]['status'],
        "amount" => $transactions[$ref]['amount']
    ]);

} else {

    echo json_encode([
        "found" => false
    ]);
}
