<?php

date_default_timezone_set("asia/tehran");

if ($token_limit <= 100 && $token_used >= 0) {
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
    $qr = "UPDATE user_tokens SET token_limit=? , token_used=? WHERE token=?";
    $statement = $conn->prepare($qr);
    $statement->execute([$token_limit, $token_used, $token]);
} else {
    $coinList['result'] = 8;
    $coinList['message'] = "429-too many requests";
    echo json_encode($coinList);
}

?>