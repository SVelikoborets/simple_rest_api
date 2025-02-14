<?php
header('Content-Type: application/json; charset=utf-8;');

$dataFile = 'data.txt';
$logFile = 'log.txt';
$largeLogFile = 'large_requests_log.txt';
$maxDataSize = 10 * 1024 * 1024;
$cacheLifetime = 3600;
$cacheFile = 'cache.json';
function getClientIP()
{
    return $_SERVER['REMOTE_ADDR'];
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {

    $input = file_get_contents('php://input');

    if (strlen($input) > $maxDataSize) {
        http_response_code(413);
        echo json_encode(['error' => 'Размер данных слишком велик']);

        $newLog = date('Y-m-d H:i:s') . ' | IP: ' . getClientIP() . ' | Size: ' . strlen($input) . "\n";
        file_put_contents($largeLogFile, $newLog, FILE_APPEND);
        exit;
    }

    $data = json_decode($input, true);

    if(mb_strlen($data['text'] ) > 1000) {
        http_response_code(400);
        echo json_encode(['error' => 'Длинна строки превысила лимит в 1000 символов']);
        exit;
    }

    file_put_contents($dataFile, $data['text'] . "\n", FILE_APPEND);

    $newLog = date('Y-m-d H:i:s') .
        '| IP:' . $_SERVER['REMOTE_ADDR'] .
        '| Length:' . strlen($data['text']) . "\n";

    file_put_contents($logFile, $newLog, FILE_APPEND );

    // Сброс кэша
    if (file_exists($cacheFile)) {
        unlink($cacheFile);
    }

    echo json_encode(['success' => 'Данные записаны']);
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'GET') {

    if(getClientIP()!=='127.0.0.1') {

        http_response_code(403);
        echo json_encode(['error' => 'Доступ запрещен']);
        exit;
    }

    if(!file_exists($dataFile)) {

        http_response_code(404);
        echo json_encode(['error' => 'Файл не найден']);
        exit;
    }

    if (file_exists($cacheFile) && filemtime($cacheFile) > filemtime($dataFile) - $cacheLifetime) {
    // Чтение данных из кэша
        $cacheData = json_decode(file_get_contents($cacheFile), true);
        $data = implode("\n", $cacheData);
    } else {
    // Чтение данных из файла
        $lines = file($dataFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    // Получение параметров limit и offset из GET-запроса
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 100;
        $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;

    // Обрезка данных по параметрам limit и offset
        $dataPart = array_slice($lines, $offset, $limit);

    // Сохранение данных в кэш
        file_put_contents($cacheFile, json_encode($dataPart,  JSON_UNESCAPED_UNICODE));

        $data = implode("\n", $dataPart);
    }

    echo json_encode(['data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Некорректный метод']);
exit;
