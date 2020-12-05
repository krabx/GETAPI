<?php
error_reporting(0);
$info = file_get_contents('https://api.binance.com/api/v3/exchangeInfo');
$json_parc = json_decode($info);
$result = [];
foreach ($json_parc as $arr) {
    foreach ($arr as $obj) {
        foreach ($obj as $k => $v) {
            if ($k === 'symbol') {
                $btc = 'BTC';
                if (strstr($v, 'BTC')) {
                    $result[] = $v;
                }
            }
        }
    }
}
$database = mysqli_connect('localhost:3306', 'bogdan', 'Lost1996!', 'finhub');
    extract ($_GET);
    $fin_var = file_get_contents("https://api.binance.com/api/v3/klines?symbol=$currency_pair&interval=$timeframe&limit=1");
    $arr = json_decode($fin_var);
    $last_price = (array_pop($arr)[4]);
    $count_check = mysqli_query($database,"SELECT COUNT(*) FROM change_price WHERE key_id = '$key_id' AND currency_pair = '$currency_pair' AND timeframe = '$timeframe'");
    $res = mysqli_fetch_row($count_check);
    if (empty($_GET['key_id']) || empty($_GET['currency_pair']) || empty($_GET['timeframe'])){
        echo 'Вы ввели не все данные';
        die;
    }
    elseif ($res[0] < 1 && !empty ($_GET['key_id']) && !empty ($_GET['currency_pair']) && !empty ($_GET['timeframe'])){
        $insert = "INSERT INTO `change_price` (key_id, currency_pair, timeframe, last_price) VALUES ('$key_id', '$currency_pair', '$timeframe', '$last_price')";
        mysqli_query($database,$insert);
        echo 'Первый запрос. Цена на текущий момент составляет' . ' ' . $last_price;
    }
    elseif ($res[0] >=1 && !empty ($_GET['key_id']) && !empty ($_GET['currency_pair']) && !empty ($_GET['timeframe'])){
        $change_price = mysqli_query($database,"SELECT `last_price` FROM change_price WHERE key_id = '$key_id' AND `currency_pair` = '$currency_pair' AND timeframe = '$timeframe'");
        $res_change_price = mysqli_fetch_row($change_price)[0];
        echo 'Обновили ценник.' . ' ' . 'Изменение цены с последнего запроса составляет' . ' ' . $delta = round((100*(($last_price - $res_change_price)/$res_change_price)),3) . '%';
        $update = "UPDATE `change_price` SET `last_price` = '$last_price' WHERE `key_id` = '$key_id' AND `currency_pair` = '$currency_pair' AND timeframe = '$timeframe'";
        mysqli_query($database,$update);
    }
?>

