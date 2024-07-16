<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// $app->post('/user', function (Request $request, Response $response) {
//     $conn = $GLOBALS['connect'];

//     $json = $request->getBody();
//     $jsonData = json_decode($json, true);

//     if (isset($jsonData['fname'])) {
//         $fname = $jsonData['fname'];
//     } else {
//         $fname = ''; 
//     }

//     $sql = 'INSERT INTO register (fname) VALUES (?)';
//     echo $sql;
//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param('s', $fname);

//     if ($stmt->execute()) {
//         $data = ["message" => "Registration successful"];
//         $response->getBody()->write(json_encode($data));
//         return $response
//             ->withHeader('Content-Type', 'application/json')
//             ->withStatus(200);
//     } else {
//         $data = ["message" => "Registration failed"];
//         $response->getBody()->write(json_encode($data));
//         return $response
//             ->withHeader('Content-Type', 'application/json')
//             ->withStatus(500);
//     }
// });
$app->post('/member', function (Request $request, Response $response, $args) {
    $json = $request->getBody();
    $jsonData = json_decode($json, true);
    $conn = $GLOBALS['connect'];

    $sql = "INSERT INTO `tbl_member` (`firstname`, `lastname`, `email`, `password`, `birthday`, `phone`) VALUES (?,?,?,?,?,?)";

    $hashedPassword = password_hash($jsonData['password'], PASSWORD_DEFAULT);

    $bdate = date("Y-m-d", strtotime($jsonData['birthday']));

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssss', $jsonData['firstname'], $jsonData['lastname'], $jsonData['email'], $hashedPassword, $bdate, $jsonData['phone']);
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

$app->get('/user', function (Request $request, Response $response) {
    $conn = $GLOBALS['connect'];
    $sql = 'SELECT * FROM `register`';
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