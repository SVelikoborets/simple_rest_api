<?php
header('Content-Type: application/json');

$dataFile = 'data.txt';
$logFile = 'log.txt';

function getClientIP()
{
    return $_SERVER['REMOTE_ADDR'];
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if(strlen($data['text'] ) > 1000) {
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

    $data = file_get_contents($dataFile);
    echo json_encode(['data' => $data]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Неверный метод']);
exit;
