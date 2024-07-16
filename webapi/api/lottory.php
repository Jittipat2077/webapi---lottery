<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/lottory', function (Request $request, Response $response) {
    $conn = $GLOBALS['connect'];
    $sql = 'select * from lottory';
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

$app->post('/lottery/search', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS['connect'];
    $requestData = json_decode($request->getBody(), true);
    $lot_number = $requestData['lot_number']; // รับค่า lot_number จาก requestBody
    $set_no = $requestData['set_no']; // รับค่า set_no จาก requestBody
    $lot_no = $requestData['lot_no']; // รับค่า lot_no จาก requestBody
    
    $sql = 'SELECT * FROM lottory WHERE SUBSTRING(lottory, -6) = ? AND lot_no = ? AND set_no = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $lot_number, $lot_no, $set_no);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $data = array();
        foreach ($result as $row) {
            array_push($data, $row);
        }

        $response = $response->withHeader('Content-Type', 'application/json; charset=utf-8');
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
        return $response;
    } else {
        return $response->withStatus(404);
    }
});

// $app->get('/country/{id}', function (Request $request, Response $response, $args) {
//     $conn = $GLOBALS['connect'];
//     $sql = 'select * from country where idx = ?';
//     $stmt = $conn->prepare($sql);

//     $stmt->bind_param('s', $args['id']);
//     $stmt->execute();
//     $result = $stmt->get_result();
//     $data = array();
//     foreach ($result as $row) {
//         array_push($data, $row);
//     }

//     $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
//     return $response
//         ->withHeader('Content-Type', 'application/json; charset=utf-8')
//         ->withStatus(200);
// });

// $app->get('/lottory/lottory/{lottory}', function (Request $request, Response $response, $args) {
//     $idx = '%'.$args['lottory'].'%';
//     $conn = $GLOBALS['connect'];
//     $sql = 'select * from lottory where lottory like ?';
//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param('s', $idx);
//     $stmt->execute();
//     $result = $stmt->get_result();
//     $data = [];
//     while ($row = $result->fetch_assoc()) {
//         array_push($data, $row);
//     }
//     $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
//     return $response
//         ->withHeader('Content-Type', 'application/json; charset=utf-8')
//         ->withStatus(200);
// });


// $app->post('/country', function (Request $request, Response $response, $args) {
//     $json = $request->getBody();
//     $jsonData = json_decode($json, true);

//     $conn = $GLOBALS['connect'];
//     $sql = 'insert into country (name) values (?)';
//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param('s', $jsonData['name']);
//     $stmt->execute();
//     $affected = $stmt->affected_rows;
//     if ($affected > 0) {

//         $data = ["affected_rows" => $affected, "last_idx" => $conn->insert_id];
//         $response->getBody()->write(json_encode($data));
//         return $response
//             ->withHeader('Content-Type', 'application/json')
//             ->withStatus(200);
//     }
// });

// $app->put('/country/{id}', function (Request $request, Response $response, $args) {
//     $json = $request->getBody();
//     $jsonData = json_decode($json, true);
//     $id = $args['id'];
//     $conn = $GLOBALS['connect'];
//     $sql = 'update country set name=? where idx = ?';
//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param('si', $jsonData['name'], $id);
//     $stmt->execute();
//     $affected = $stmt->affected_rows;
//     if ($affected > 0) {
//         $data = ["affected_rows" => $affected];
//         $response->getBody()->write(json_encode($data));
//         return $response
//             ->withHeader('Content-Type', 'application/json')
//             ->withStatus(200);
//     }
// });

// $app->delete('/country/{id}', function (Request $request, Response $response, $args) {
//     $id = $args['id'];
//     $conn = $GLOBALS['connect'];
//     $sql = 'delete from country where idx = ?';
//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param('i', $id);
//     $stmt->execute();
//     $affected = $stmt->affected_rows;
//     if ($affected > 0) {
//         $data = ["affected_rows" => $affected];
//         $response->getBody()->write(json_encode($data));
//         return $response
//             ->withHeader('Content-Type', 'application/json')
//             ->withStatus(200);
//     }
// });
