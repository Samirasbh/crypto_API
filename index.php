
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
        if (!$res) {
            $coinList['result'] = 8;
            $coinList['message'] = "403-forbiden";
            echo json_encode($coinList);
        } else {
            $token = $res['token'];
            $token_limit = $res['token_limit'];
            $token_used = $res['token_used'];
            $wi = $res['white_ip'];
            $bi = $res['black_ip'];
            if ($token_limit <= 100 && $token_used >= 0) {
                session_start();
                if (isset($_GET['offset']) && isset($_GET['limit'])) {
                    $limit = $_GET['limit'];
                    $offset = $_GET['offset'];
                    $sql = "SELECT * FROM crypto ORDER BY id ASC LIMIT $limit OFFSET $offset";
                    $stm = $conn->prepare($sql);
                    $stm->execute();
                    $result = $stm->fetchAll();
                    foreach ($result as $r) {
                        array_push($coinList['data'], ["name" => $r['Name'], "price" => $r['PRICE'], "symbol" => $r['Symbol']]);
                    }
                    $json = json_encode($coinList);
                    echo $json;
                } else {
                    $sql = "SELECT * FROM crypto ORDER BY id DESC LIMIT 100 OFFSET 0";
                    $stm = $conn->prepare($sql);
                    $stm->execute();
                    $result = $stm->fetchAll();
                    foreach ($result as $r) {
                        array_push($coinList['data'], ["name" => $r['Name'], "price" => $r['PRICE'], "symbol" => $r['Symbol']]);
                    }
                    $json = json_encode($coinList);
                    echo $json;
                }
                $token_limit--;
                $token_used++;
                $qr = "UPDATE user_tokens SET token_limit = ?,token_used=? WHERE token =$token";
                $statement = $conn->prepare($qr);
                $statement->execute([$token_limit, $token_used]);
            }else{
                $coinList['result'] = 8;
                $coinList['message'] = "429-too many requests";
                echo json_encode($coinList);
            }
        }
    }
}
?>