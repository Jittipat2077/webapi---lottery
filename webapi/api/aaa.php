<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


$app->get('/aa', function (Request $request, Response $response) {
    $conn = $GLOBALS['connect'];
    $sql = 'SELECT * FROM aaa';
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = array();
    foreach ($result as $row) {
        array_push($data, $row);
    }
    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
    return $response->withHeader('Content-Type', 'application/json; charset=utf-8')->withStatus(200);
});



$app->post('/aaa', function (Request $request, Response $response, $args) {
    $jsonData = $request->getParsedBody();
    $conn = $GLOBALS['connect'];

    $sql = "INSERT INTO aaa (`num`) VALUES (?)";


    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $jsonData['num']);
    $stmt->execute();

    $affected = $stmt->affected_rows;
    if ($affected > 0) {
        $data = ["affected_rows" => $affected, "last_idx" => $conn->insert_id];
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    } else {
        $data = ["error" => "มีข้อผิดพลาดเกิดขึ้น"];
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }
});
