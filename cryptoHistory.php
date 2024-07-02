<?php


//  connect to databasse
$servername = 'localhost';
$dbname = 'ahrom_for_passing';
$username = 'root';
$password = '123';
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "connected successfully \n";
} catch (PDOException $e) {
    echo "connection failed :" . $e->getMessage();
}

$coinList = [
    "symbol" => "",
    "data" => []
];
if (isset($_GET['symbol']) && isset($_GET['from']) && isset($_GET['to'])) {
    $sql = "SELECT * FROM crypto_historical_data WHERE symbol=? AND time BETWEEN ? AND ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_GET['symbol'], $_GET['from'], $_GET['to']]);
    $res = $stmt->fetchAll();
    $coinList['symbol'] = $_GET['symbol'];
    foreach ($res as $r) {
        array_push($coinList['data'], [
            "time" => $r['time'], "close" => $r['close'], "open" => $r['open'], "high" => $r['high'], "low" => $r['low'],
            "volumefrom" => $r['volumefrom'], "volumeto" => $r['volumeto']
        ]);
    }
    echo json_encode($coinList);
} elseif (!isset($_GET['symbol']) && !isset($_GET['from']) && !isset($_GET['to'])) {
    echo "symbol or time scope is not entered";

} elseif (!isset($_GET['symbol']) && isset($_GET['from']) && isset($_GET['to'])) {
    echo "symbol is not entered";

} elseif (
    (isset($_GET['symbol']) && !isset($_GET['from']) && !isset($_GET['to'])) ||
    (isset($_GET['symbol']) && !isset($_GET['from']) && isset($_GET['to'])) ||
    (isset($_GET['symbol']) && isset($_GET['from']) && !isset($_GET['to']))) {
    echo "enter time scope";
}
