<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/landmark', function (Request $request, Response $response) {
    $conn = $GLOBALS['connect'];
    $sql = 'select landmark.idx, landmark.name,landmark.detail,landmark.url,
    country.name as country from landmark inner join country on landmark.country = country.idx';
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = array();
    foreach ($result as $row) {
        array_push($data, $row);
    }

    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
    return $response
        ->withHeader('Content-Type', 'application/json; charset=utf-8')
        ->withStatus(200);
});

$app->get('/landmark/country/{country}', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS['connect'];

    
    $sql = 'select landmark.idx, landmark.name,landmark.detail,landmark.url,
    country.name as country from landmark inner join country on landmark.country = country.idx where country.name like ?';
    $stmt = $conn->prepare($sql);
    $name = '%' . $args['country'] . '%';
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = array();
    foreach ($result as $row) {
        array_push($data, $row);
    }

    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
    return $response
        ->withHeader('Content-Type', 'application/json; charset=utf-8')
        ->withStatus(200);
});

$app->get('/landmark/{idx}', function (Request $request, Response $response, $args) {
    $idx = $args['idx'];
    $conn = $GLOBALS['connect'];
    $sql = 'select landmark.idx, landmark.name,landmark.detail,landmark.url,
    country.name as country from landmark inner join country on landmark.country = country.idx where landmark.idx = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $idx);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        array_push($data, $row);
    }
    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
    return $response
        ->withHeader('Content-Type', 'application/json; charset=utf-8')
        ->withStatus(200);
});


$app->post('/landmark', function (Request $request, Response $response, $args) {
    $json = $request->getBody();
    $jsonData = json_decode($json, true);

    $conn = $GLOBALS['connect'];
    $sql = 'insert into landmark (name, country, detail, url) values (?, ?, ?, ?)';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssss', $jsonData['name'], $jsonData['country'], $jsonData['detail'], $jsonData['url']);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    if ($affected > 0) {

        $data = ["affected_rows" => $affected, "last_idx" => $conn->insert_id];
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
});

$app->put('/landmark/{id}', function (Request $request, Response $response, $args) {
    $json = $request->getBody();
    $jsonData = json_decode($json, true);
    $id = $args['id'];
    $conn = $GLOBALS['connect'];
    $sql = 'update landmark set name=?, country=?, detail=?, url=? where idx = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssi', $jsonData['name'], $jsonData['country'], $jsonData['detail'], $jsonData['url'], $id);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    if ($affected > 0) {
        $data = ["affected_rows" => $affected];
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
});

$app->delete('/landmark/{id}', function (Request $request, Response $response, $args) {
    $id = $args['id'];
    $conn = $GLOBALS['connect'];
    $sql = 'delete from landmark where idx = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    if ($affected > 0) {
        $data = ["affected_rows" => $affected];
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
});
