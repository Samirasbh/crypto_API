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
    "result" => 1,
    "message" => "success",
    "data" => []
];


/* **      insert white ip or black ip in database     ** */
// $ipList = [
//     "ip"=>["127.0.0.1","100.100.100.100","::1"]
// ];
// $ipJson = json_encode($ipList);
// $ipSQL = "UPDATE user_tokens SET whiteIP = ? WHERE id = ?";
// $ipstm = $conn->prepare($ipSQL);
// $ipstm->execute([$ipJson , 1]);


// check if token entered in url or not
if (!isset($_GET['UT'])) {
    $coinList['result'] = 8;
    $coinList['message'] = "403-forbiden";
    echo json_encode($coinList);
} else {
    if (isset($_GET['UT'])) {
        // check if user token exists in database or not
        $userToken = $_GET['UT'];
        $query = "SELECT * FROM user_tokens WHERE token =?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$userToken]);
        $res = $stmt->fetch();
        // if there is no user token in database
        if (!$res) {
            $coinList['result'] = 8;
            $coinList['message'] = "403-forbiden";
            echo json_encode($coinList);
        } 
        // if there is a user token in database
        else {
            $token = $res['token'];
            $token_limit = $res['token_limit'];
            $token_used = $res['token_used'];
            $wi = $res['whiteIP'];
            $bi = $res['blackIP'];
            $token_count = $res['token_count'];
            // if white ip and black ip is empty in database
            if (empty($wi) && empty($bi)) {
                require_once("./showCoin.php");
            } else {
                // user ip
                $userIP = $_SERVER["REMOTE_ADDR"];
                // if white ip is filled in database
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
                        require_once("./showCoin.php");
                    } else {
                        $coinList['result'] = 8;
                        $coinList['message'] = "403-forbiden";
                        echo json_encode($coinList);
                    }
                }
                // if black ip is filled in database
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
                        $coinList['result'] = 8;
                        $coinList['message'] = "403-forbiden";
                        echo json_encode($coinList);
                    } else {
                       require_once("./showCoin.php");
                    }
                }
            }
        }
    }
}
?>