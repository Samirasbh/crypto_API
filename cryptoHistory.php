

<?php

date_default_timezone_set('asia/tehran');
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

// beginning and end of today timestamp
$time = time();
$beginning_of_day = strtotime("midnight", $time);

// get token from postman
$headers = getallheaders();
$token = (str_replace("Bearer ", "", $headers["Authorization"]));

// check if token exists or not
if (!$token) {
    echo "token is not entered";
    exit;
}
$qr = "SELECT * FROM user_tokens WHERE token = ?";
$st = $conn->prepare($qr);
$st->execute([$token]);
$result = $st->fetch();

if (!$result) {
    echo "token does'nt match to database";
    exit;
}
$token_limit = $result['token_limit'];
$token_used = $result['token_used'];
$wi = $result['whiteIP'];
$bi = $result['blackIP'];

// check token limit and used 
if ($token_limit < 0 && $token_used > 100) {
    echo "token limit invalid";
    exit;
}
// check wihte ips and black ips

    // check if symbol or time scope entered or not
    if (!isset($_GET['symbol']) && !isset($_GET['from']) && !isset($_GET['to'])) {
        echo "symbol or time scope is not entered";
        exit;
    }
    if (!isset($_GET['symbol']) && isset($_GET['from']) && isset($_GET['to'])) {
        echo "symbol is not entered";
        exit;
    }
    if ((isset($_GET['symbol']) && !isset($_GET['from']) && !isset($_GET['to'])) ||
        (isset($_GET['symbol']) && !isset($_GET['from']) && isset($_GET['to'])) ||
        (isset($_GET['symbol']) && isset($_GET['from']) && !isset($_GET['to']))
    ) {
        echo "enter time scope";
        exit;
    }

if (empty($wi) && empty($bi)) {

    // if symbol and time scope entered
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
        // if time range is until today
        if ($_GET['to'] >= $beginning_of_day) {
            $qr = "SELECT * FROM crypto WHERE symbol= ?";
            $stm = $conn->prepare($qr);
            $stm->execute([$_GET['symbol']]);
            $result = $stm->fetch();
            $coin = [
                "time" => $beginning_of_day, "close" => $result['PRICE'], "open" => $result['OPENDAY'], "high" => $result['HIGHDAY'], "low" => $result['LOWDAY'],
                "volumefrom" => $result['VOLUMEDAY'], "volumeto" => $result['VOLUMEDAYTO']
            ];
        }
        array_push($coinList['data'], $coin);
        echo json_encode($coinList);
    }
    exit;
}
// check white ip , if white ip is not empty do this
$userIP = $_SERVER["REMOTE_ADDR"];

if (!empty($wi)) {
    $white = false;
    // white ips from database
    $wIP = json_decode($wi, true);
    $wIPList = $wIP['ip'];
    // check if user ip is in white ip list or not
    foreach ($wIPList as $ip) {
        if ($userIP == $ip) {
            $white = true;
            break;
        }
    }
    if ($white == false) {
        echo "user ip is invalid";
        exit;
    }

    // if symbol and time scope entered
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
        // if time range is until today
        if ($_GET['to'] >= $beginning_of_day) {
            $qr = "SELECT * FROM crypto WHERE symbol= ?";
            $stm = $conn->prepare($qr);
            $stm->execute([$_GET['symbol']]);
            $result = $stm->fetch();
            $coin = [
                "time" => $beginning_of_day, "close" => $result['PRICE'], "open" => $result['OPENDAY'], "high" => $result['HIGHDAY'], "low" => $result['LOWDAY'],
                "volumefrom" => $result['VOLUMEDAY'], "volumeto" => $result['VOLUMEDAYTO']
            ];
        }
        array_push($coinList['data'], $coin);
        echo json_encode($coinList);
    }
}
// check white ip , if white ip is not empty do this
if (!empty($bi)) {
    $black = false;
    // black ips from database
    $bIP = json_decode($bi, true);
    $bIPList = $bIP['ip'];
    // check if user ip is in black ip list or not
    foreach ($bIPList as $ip) {
        if ($userIP = $ip) {
            $black = true;
            break;
        }
    }
    if ($black == true) {
        echo "user ip is invalid";
        exit;
    }

    // if symbol and time scope entered
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
        // if time range is until today
        if ($_GET['to'] >= $beginning_of_day) {
            $qr = "SELECT * FROM crypto WHERE symbol= ?";
            $stm = $conn->prepare($qr);
            $stm->execute([$_GET['symbol']]);
            $result = $stm->fetch();
            $coin = [
                "time" => $beginning_of_day, "close" => $result['PRICE'], "open" => $result['OPENDAY'], "high" => $result['HIGHDAY'], "low" => $result['LOWDAY'],
                "volumefrom" => $result['VOLUMEDAY'], "volumeto" => $result['VOLUMEDAYTO']
            ];
        }
        array_push($coinList['data'], $coin);
        echo json_encode($coinList);
    }
    

}
