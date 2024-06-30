<?php

session_start();
$duration = 600;   // second
if (!$_SESSION['login_time']){
    $_SESSION['login_time'] = $_SERVER['REQUEST_TIME'];   //login time
}

// check if user can use token or not , if token limit still is accessible in range 0 to 100
if ($token_limit <= 100 && $token_used >= 0) {

    // check timeout for session user entered in api
    if (isset($_SESSION['LAST_ACTIVITY']) && ($_SESSION['login_time'] - $_SESSION['LAST_ACTIVITY']) > $duration) {
        $token_count = 0;
        $qr = "UPDATE user_tokens SET token_count =? WHERE token=?";
        $statement = $conn->prepare($qr);
        $statement->execute([$token_count, $token]);
        session_unset();
        session_destroy();
        // for example redirect to google .actually it should redirect to login page .
        header('location:https://www.google.com');
        exit;
    }
    $_SESSION['LAST_ACTIVITY'] = $_SESSION['login_time'];


    // check token usage in an hour / 10 times can use a single token in an hour
    if (time() - $_SESSION['login_time'] <= 60) {

        // echo time() . "</br>";
        // echo $_SESSION['login_time'] . "</br>";
        // echo $token_count . "</br>";
        if ($token_count < 10) {
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
            $token_count++;
            $token_limit--;
            $token_used++;
            $qr = "UPDATE user_tokens SET token_limit=? , token_used=? , token_count =? WHERE token=?";
            $statement = $conn->prepare($qr);
            $statement->execute([$token_limit, $token_used, $token_count, $token]);
        }
        if ($token_count >= 10) {
            $coinList['result'] = 8;
            $coinList['message'] = "429-too many requests";
            echo json_encode($coinList);
        }
    } else {
        if (time() - $_SESSION['login_time'] > 60) {
            $token_count = 0;
            $qr = "UPDATE user_tokens SET token_count =? WHERE token=?";
            $statement = $conn->prepare($qr);
            $statement->execute([$token_count, $token]);
            session_unset();
            session_destroy();
            header('location:https://www.google.com');
            exit;
        }
    }
}
// if token limit is not accessible / oute of 100 times
else {
    $coinList['result'] = 8;
    $coinList['message'] = "429-too many requests";
    echo json_encode($coinList);
}
