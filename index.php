<?php
header('Content-Type: application/json');

$dataFile = 'data.txt';
$logFile = 'log.txt';
$largeLogFile = 'large_requests_log.txt';
$maxDataSize = 10 * 1024 * 1024;
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

    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 100;
    $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;

// Чтение данных из файла
    $lines = file($dataFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// Обрезка данных по параметрам limit и offset
    $data = array_slice($lines, $offset, $limit);

    echo json_encode(['data' => implode(PHP_EOL, $data)]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Неверный метод']);
exit;