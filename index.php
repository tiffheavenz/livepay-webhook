<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "START<br>";

try {

    $pdo = new PDO(
        "mysql:host=sql309.byetcluster.com;dbname=b5_42074304_shjeeeee;charset=utf8mb4",
        "b5_42074304",
        "tiffheavenz"
    );

    echo "CONNECTED";

} catch (Throwable $e) {

    echo $e->getMessage();

}

?>
