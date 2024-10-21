<?php
function postResponse($url, $data)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    $res = curl_exec($curl);

    if ($res === false) {
    return ['error' => curl_error($curl)];
    } else {
    return json_decode($res, true);
    }
}

function response($url)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $res = curl_exec($curl);

    if ($res === false) {
    return ['error' => curl_error($curl)];
    } else {
    return json_decode($res, true);
    }
}

$quotes = include 'yoda.php';

$random_index = array_rand($quotes);

// Получаем цитату по случайному индексу
$yoda_quote = $quotes[$random_index];

$data = json_encode(['text' => $yoda_quote]);

$url = 'http://' . $_SERVER['SERVER_NAME'] . '/api/index.php';

echo " Запишем данные в файл:\n";

$res = postResponse($url, $data);
echo '<pre>';
print_r($res);
echo '</pre>';

$url = 'http://' . $_SERVER['SERVER_NAME'] . '/api/index.php?limit=10&offset=0';

echo "Прочитаем данные из файла:\n";
$res = response($url);
echo '<pre>';
print_r($res);
echo '</pre>';

echo "Спасибо за просмотр!";