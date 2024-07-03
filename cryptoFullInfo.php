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
// crypto table row count
$query = "SELECT COUNT(*) FROM crypto";
$stmt = $conn->prepare($query);
$stmt->execute();
$count = $stmt->fetch();

$coinList = [
    "count" => $count['0'],
    "limit" => "",
    "currencies" => []
];
// get token from postman
$headers = getallheaders();
$token = (str_replace("Bearer ", "", $headers["Authorization"]));

// check if token entered or not
if (!$token) {
    echo "token is not entered";
    exit;
}

$qr = "SELECT * FROM user_tokens WHERE token = ?";
$st = $conn->prepare($qr);
$st->execute([$token]);
$result = $st->fetch();

// if entered token does not match to database
if (!$result) {
    echo "token does'nt match to database";
    exit;
}
// if entered token match to database
$token_limit = $result['token_limit'];
$token_used = $result['token_used'];
$wi = $result['whiteIP'];
$bi = $result['blackIP'];

if ($token_limit < 0  && $token_used > 100) {
    echo "token limit invalid";
    exit;
}
//------------------- if condition may be solved with XOR---------------------------
if ((isset($_GET['limit']) && !isset($_GET['offset'])) || (!isset($_GET['limit']) && isset($_GET['offset']))) {
    echo 'limit or offset is not entered';
    exit;
}
if (isset($_GET['limit']) && isset($_GET['offset']) && isset($_GET['symbol'])) {
    echo "multiple action - access denied";
    exit;
}
// if white ips and black ips of entered user token is empty
if (empty($wi) && empty($bi)) {
    if (!isset($_GET['symbol'])) {
        // limit and offset assignment
        $limit = isset($_GET['limit']) ? $_GET['limit'] : 500;
        $offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
        $coinList['limit'] = $limit;

        // if limit and offset are not numeric /condition:  1<limit<500 / offset>0
        if (!is_numeric($limit) || !is_numeric($offset) || $limit < 1 || $limit > 500 || $offset < 0) {
            echo 'invalid parameters - limit or offset';
            exit;
        }

        if (is_numeric($limit) && is_numeric($offset) && $limit >= 1 && $limit <= 500 && $offset >= 0) {

            $sql = "SELECT * FROM crypto order by id ASC limit $limit OFFSET $offset";
            $stm = $conn->prepare($sql);
            $stm->execute();
            $res = $stm->fetchAll();
            foreach ($res as $r) {
                array_push($coinList['currencies'], [
                    "CoinName" => $r['CoinName'], "Symbol" => $r['Symbol'], "LASTUPDATE" => $r['LASTUPDATE'],
                    "Description" => $r['Description'], "PRICE" => $r['PRICE'], "OPENDAY" => $r["OPENDAY"], "HIGHDAY" => $r['HIGHDAY'],
                    "VOLUME24HOUR" => $r['VOLUME24HOUR'], "VOLUME24HOURTO" => $r['VOLUME24HOURTO'], "CHANGE24HOUR" => $r['CHANGE24HOUR'],
                    "CHANGEPCT24HOUR" => $r['CHANGEPCT24HOUR'], "CHANGEDAY" => $r['CHANGEDAY'], "CHANGEPCTDAY" => $r['CHANGEPCTDAY'],
                    "MKTCAP" => $r['MKTCAP'], "SUPPLY" => $r['SUPPLY'], "ath" => $r['ath'], "atl" => $r['atl'], "ImageUrl" => $r['ImageUrl']
                ]);
            }
            echo json_encode($coinList);
        }
    }
    // if just set symbol 
    if (isset($_GET['symbol'])) {
        $symbol = $_GET['symbol'];
        $sql = "SELECT * FROM crypto WHERE Symbol = ?";
        $stm = $conn->prepare($sql);
        $stm->execute([$symbol]);
        $r = $stm->fetch();
        if (!$r) {
            echo "invalid coin";
            exit;
        }
        array_push($coinList['currencies'], [
            "CoinName" => $r['CoinName'], "Symbol" => $r['Symbol'], "LASTUPDATE" => $r['LASTUPDATE'],
            "Description" => $r['Description'], "PRICE" => $r['PRICE'], "OPENDAY" => $r["OPENDAY"], "HIGHDAY" => $r['HIGHDAY'],
            "VOLUME24HOUR" => $r['VOLUME24HOUR'], "VOLUME24HOURTO" => $r['VOLUME24HOURTO'], "CHANGE24HOUR" => $r['CHANGE24HOUR'],
            "CHANGEPCT24HOUR" => $r['CHANGEPCT24HOUR'], "CHANGEDAY" => $r['CHANGEDAY'], "CHANGEPCTDAY" => $r['CHANGEPCTDAY'],
            "MKTCAP" => $r['MKTCAP'], "SUPPLY" => $r['SUPPLY'], "ath" => $r['ath'], "atl" => $r['atl'], "ImageUrl" => $r['ImageUrl']
        ]);
        echo json_encode($coinList['currencies']);
    }
    $token_limit--;
    $token_used++;
    $qr1 = "UPDATE user_tokens SET token_limit=? , token_used=?  WHERE token=?";
    $statement = $conn->prepare($qr1);
    $statement->execute([$token_limit, $token_used, $token]);
    exit;
}


$userIP = $_SERVER["REMOTE_ADDR"];
// if white ips is not empty
$white = false;
if (!empty($wi)) {
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
}

// if black ips is not empty
$black = false;
if (!empty($bi)) {
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
}
if ($white == false || $black == true) {
    echo "user ip is invalid";
    exit;
}
if (!isset($_GET['symbol'])) {
    // limit and offset assignment
    $limit = isset($_GET['limit']) ? $_GET['limit'] : 500;
    $offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
    $coinList['limit'] = $limit;

    // if limit and offset are not numeric /condition:  1<limit<500 / offset>0
    if (!is_numeric($limit) || !is_numeric($offset) || $limit < 1 || $limit > 500 || $offset < 0) {
        echo 'invalid parameters - limit or offset';
        exit;
    }

    if (is_numeric($limit) && is_numeric($offset) && $limit >= 1 && $limit <= 500 && $offset >= 0) {

        $sql = "SELECT * FROM crypto order by id ASC limit $limit OFFSET $offset";
        $stm = $conn->prepare($sql);
        $stm->execute();
        $res = $stm->fetchAll();
        foreach ($res as $r) {
            array_push($coinList['currencies'], [
                "CoinName" => $r['CoinName'], "Symbol" => $r['Symbol'], "LASTUPDATE" => $r['LASTUPDATE'],
                "Description" => $r['Description'], "PRICE" => $r['PRICE'], "OPENDAY" => $r["OPENDAY"], "HIGHDAY" => $r['HIGHDAY'],
                "VOLUME24HOUR" => $r['VOLUME24HOUR'], "VOLUME24HOURTO" => $r['VOLUME24HOURTO'], "CHANGE24HOUR" => $r['CHANGE24HOUR'],
                "CHANGEPCT24HOUR" => $r['CHANGEPCT24HOUR'], "CHANGEDAY" => $r['CHANGEDAY'], "CHANGEPCTDAY" => $r['CHANGEPCTDAY'],
                "MKTCAP" => $r['MKTCAP'], "SUPPLY" => $r['SUPPLY'], "ath" => $r['ath'], "atl" => $r['atl'], "ImageUrl" => $r['ImageUrl']
            ]);
        }
        echo json_encode($coinList);
    }
}
// if just set symbol 
if (isset($_GET['symbol'])) {
    $symbol = $_GET['symbol'];
    $sql = "SELECT * FROM crypto WHERE Symbol = ?";
    $stm = $conn->prepare($sql);
    $stm->execute([$symbol]);
    $r = $stm->fetch();
    if (!$r) {
        echo "invalid coin";
        exit;
    }
    array_push($coinList['currencies'], [
        "CoinName" => $r['CoinName'], "Symbol" => $r['Symbol'], "LASTUPDATE" => $r['LASTUPDATE'],
        "Description" => $r['Description'], "PRICE" => $r['PRICE'], "OPENDAY" => $r["OPENDAY"], "HIGHDAY" => $r['HIGHDAY'],
        "VOLUME24HOUR" => $r['VOLUME24HOUR'], "VOLUME24HOURTO" => $r['VOLUME24HOURTO'], "CHANGE24HOUR" => $r['CHANGE24HOUR'],
        "CHANGEPCT24HOUR" => $r['CHANGEPCT24HOUR'], "CHANGEDAY" => $r['CHANGEDAY'], "CHANGEPCTDAY" => $r['CHANGEPCTDAY'],
        "MKTCAP" => $r['MKTCAP'], "SUPPLY" => $r['SUPPLY'], "ath" => $r['ath'], "atl" => $r['atl'], "ImageUrl" => $r['ImageUrl']
    ]);
    echo json_encode($coinList['currencies']);
}
$token_limit--;
$token_used++;
$qr1 = "UPDATE user_tokens SET token_limit=? , token_used=?  WHERE token=?";
$statement = $conn->prepare($qr1);
$statement->execute([$token_limit, $token_used, $token]);