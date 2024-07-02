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
    echo "token not entered";
} else {
    $qr = "SELECT * FROM user_tokens WHERE token = ?";
    $st = $conn->prepare($qr);
    $st->execute([$token]);
    $result = $st->fetch();

    if (!$result) {
        echo "token does'nt match to database";
    } else {
        $token_limit = $result['token_limit'];
        $token_used = $result['token_used'];
        $wi = $result['whiteIP'];
        $bi = $result['blackIP'];
        if ($token_limit <= 100 && $token_used >= 0) {
            if (empty($wi) && empty($bi)) {
                if (isset($_GET['limit']) && isset($_GET['offset'])) {
                    $limit = $_GET['limit'];
                    $offset = $_GET['offset'];
                    $coinList['limit'] = $_GET['limit'];
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
                    } elseif (!is_numeric($limit) || !is_numeric($offset) || $limit < 1 || $limit > 500 || $offset < 0) {
                        $coinList = ['invalid parameters - limit or offset'];
                    }
                }
                if ((isset($_GET['limit']) && !isset($_GET['offset'])) || (!isset($_GET['limit']) && isset($_GET['offset']))) {
                    $coinList = ['limit or offset is not entered'];
                }
                if (!isset($_GET['limit']) && !isset($_GET['offset']) && !isset($_GET['symbol'])) {
                    $limit = 500;
                    $offset = 0;
                    $coinList['limit'] = 500;
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


                if (!isset($_GET['limit']) && !isset($_GET['offset']) && isset($_GET['symbol'])) {
                    $symbol = $_GET['symbol'];
                    $sql = "SELECT * FROM crypto WHERE Symbol = ?";
                    $stm = $conn->prepare($sql);
                    $stm->execute([$symbol]);
                    $r = $stm->fetch();
                    if (!$r) {
                        $coinList['currencies'] = ["invalid coin"];
                    }
                    if ($r) {
                        array_push($coinList['currencies'], [
                            "CoinName" => $r['CoinName'], "Symbol" => $r['Symbol'], "LASTUPDATE" => $r['LASTUPDATE'],
                            "Description" => $r['Description'], "PRICE" => $r['PRICE'], "OPENDAY" => $r["OPENDAY"], "HIGHDAY" => $r['HIGHDAY'],
                            "VOLUME24HOUR" => $r['VOLUME24HOUR'], "VOLUME24HOURTO" => $r['VOLUME24HOURTO'], "CHANGE24HOUR" => $r['CHANGE24HOUR'],
                            "CHANGEPCT24HOUR" => $r['CHANGEPCT24HOUR'], "CHANGEDAY" => $r['CHANGEDAY'], "CHANGEPCTDAY" => $r['CHANGEPCTDAY'],
                            "MKTCAP" => $r['MKTCAP'], "SUPPLY" => $r['SUPPLY'], "ath" => $r['ath'], "atl" => $r['atl'], "ImageUrl" => $r['ImageUrl']
                        ]);
                    }
                    echo json_encode($coinList['currencies']);
                }
            } else {
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
                    if ($white == true) {
                        if (isset($_GET['limit']) && isset($_GET['offset'])) {
                            $limit = $_GET['limit'];
                            $offset = $_GET['offset'];
                            $coinList['limit'] = $_GET['limit'];
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
                            } elseif (!is_numeric($limit) || !is_numeric($offset) || $limit < 1 || $limit > 500 || $offset < 0) {
                                $coinList = ['invalid parameters - limit or offset'];
                            }
                        }
                        if ((isset($_GET['limit']) && !isset($_GET['offset'])) || (!isset($_GET['limit']) && isset($_GET['offset']))) {
                            $coinList = ['limit or offset is not entered'];
                        }
                        if (!isset($_GET['limit']) && !isset($_GET['offset']) && !isset($_GET['symbol'])) {
                            $limit = 500;
                            $offset = 0;
                            $coinList['limit'] = 500;
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


                        if (!isset($_GET['limit']) && !isset($_GET['offset']) && isset($_GET['symbol'])) {
                            $symbol = $_GET['symbol'];
                            $sql = "SELECT * FROM crypto WHERE Symbol = ?";
                            $stm = $conn->prepare($sql);
                            $stm->execute([$symbol]);
                            $r = $stm->fetch();
                            if (!$r) {
                                $coinList['currencies'] = ["invalid coin"];
                            }
                            if ($r) {
                                array_push($coinList['currencies'], [
                                    "CoinName" => $r['CoinName'], "Symbol" => $r['Symbol'], "LASTUPDATE" => $r['LASTUPDATE'],
                                    "Description" => $r['Description'], "PRICE" => $r['PRICE'], "OPENDAY" => $r["OPENDAY"], "HIGHDAY" => $r['HIGHDAY'],
                                    "VOLUME24HOUR" => $r['VOLUME24HOUR'], "VOLUME24HOURTO" => $r['VOLUME24HOURTO'], "CHANGE24HOUR" => $r['CHANGE24HOUR'],
                                    "CHANGEPCT24HOUR" => $r['CHANGEPCT24HOUR'], "CHANGEDAY" => $r['CHANGEDAY'], "CHANGEPCTDAY" => $r['CHANGEPCTDAY'],
                                    "MKTCAP" => $r['MKTCAP'], "SUPPLY" => $r['SUPPLY'], "ath" => $r['ath'], "atl" => $r['atl'], "ImageUrl" => $r['ImageUrl']
                                ]);
                            }
                            echo json_encode($coinList['currencies']);
                        }
                    } else {
                        echo "ip is invalid";
                    }
                }
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
                        echo "ip is invalid";
                    } else {
                        if (isset($_GET['limit']) && isset($_GET['offset'])) {
                            $limit = $_GET['limit'];
                            $offset = $_GET['offset'];
                            $coinList['limit'] = $_GET['limit'];
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
                            } elseif (!is_numeric($limit) || !is_numeric($offset) || $limit < 1 || $limit > 500 || $offset < 0) {
                                $coinList = ['invalid parameters - limit or offset'];
                            }
                        }
                        if ((isset($_GET['limit']) && !isset($_GET['offset'])) || (!isset($_GET['limit']) && isset($_GET['offset']))) {
                            $coinList = ['limit or offset is not entered'];
                        }
                        if (!isset($_GET['limit']) && !isset($_GET['offset']) && !isset($_GET['symbol'])) {
                            $limit = 500;
                            $offset = 0;
                            $coinList['limit'] = 500;
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


                        if (!isset($_GET['limit']) && !isset($_GET['offset']) && isset($_GET['symbol'])) {
                            $symbol = $_GET['symbol'];
                            $sql = "SELECT * FROM crypto WHERE Symbol = ?";
                            $stm = $conn->prepare($sql);
                            $stm->execute([$symbol]);
                            $r = $stm->fetch();
                            if (!$r) {
                                $coinList['currencies'] = ["invalid coin"];
                            }
                            if ($r) {
                                array_push($coinList['currencies'], [
                                    "CoinName" => $r['CoinName'], "Symbol" => $r['Symbol'], "LASTUPDATE" => $r['LASTUPDATE'],
                                    "Description" => $r['Description'], "PRICE" => $r['PRICE'], "OPENDAY" => $r["OPENDAY"], "HIGHDAY" => $r['HIGHDAY'],
                                    "VOLUME24HOUR" => $r['VOLUME24HOUR'], "VOLUME24HOURTO" => $r['VOLUME24HOURTO'], "CHANGE24HOUR" => $r['CHANGE24HOUR'],
                                    "CHANGEPCT24HOUR" => $r['CHANGEPCT24HOUR'], "CHANGEDAY" => $r['CHANGEDAY'], "CHANGEPCTDAY" => $r['CHANGEPCTDAY'],
                                    "MKTCAP" => $r['MKTCAP'], "SUPPLY" => $r['SUPPLY'], "ath" => $r['ath'], "atl" => $r['atl'], "ImageUrl" => $r['ImageUrl']
                                ]);
                            }
                            echo json_encode($coinList['currencies']);
                        }
                    }
                }
            }
            $token_limit--;
            $token_used++;
            $qr1 = "UPDATE user_tokens SET token_limit=? , token_used=?  WHERE token=?";
            $statement = $conn->prepare($qr1);
            $statement->execute([$token_limit, $token_used, $token]);
        } else {
            echo "invalid token limit";
        }
    }
}
