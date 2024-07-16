<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->post('/cart', function (Request $request, Response $response, $args) {
    $json = $request->getBody();
    $jsonData = json_decode($json, true);
    $conn = $GLOBALS['connect'];

    $sql = "INSERT INTO `tbl_cart`(`num`, `amount`, `price`, `lot_no`, `set_no`, `price_all`, `mem_id`) VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssss', $jsonData['num'], $jsonData['amount'], $jsonData['price'], $jsonData['lot_no'], $jsonData['set_no'], $jsonData['price_all'], $jsonData['mem_id']);
    $stmt->execute();

    $affected = $stmt->affected_rows;
    if ($affected > 0) {
        $data = ["affected_rows" => $affected, "last_id" => $conn->insert_id];
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    } else {
        // บันทึกไม่สำเร็จ
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(500);
    }
});

$app->get('/cart-show', function (Request $request, Response $response) {
    $conn = $GLOBALS['connect'];
    $sql = 'SELECT * FROM tbl_cart';
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

$app->get('/cart-show/{mem_id}', function (Request $request, Response $response, $args) {
    $mem_id = $args['mem_id'];
    $conn = $GLOBALS['connect'];
    $sql = 'SELECT * FROM tbl_cart WHERE mem_id = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $mem_id);
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




$app->delete('/cart-delete/{id}', function (Request $request, Response $response, $args) {
    $id = $args['id']; // รับค่า id จาก URL
    $conn = $GLOBALS['connect'];
    $stmt = $conn->prepare("DELETE FROM tbl_cart WHERE id=?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->affected_rows;

    if ($result > 0) {
        // ลบสำเร็จ
        return $response
            ->withStatus(204); // 204 No Content
    } else {
        // ไม่พบรายการหรือมีข้อผิดพลาด
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(404);
    }
});

$app->delete('/cart-clear/{id}', function (Request $request, Response $response, $args) {
    $id = $args['id']; // รับค่า id จาก URL
    $conn = $GLOBALS['connect'];
    $stmt = $conn->prepare("DELETE FROM tbl_cart WHERE id = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->affected_rows;

    if ($result > 0) {
        // ลบสำเร็จ
        return $response
            ->withStatus(204); // 204 No Content
    } else {
        // ไม่พบรายการหรือมีข้อผิดพลาด
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(404);
    }
});

$app->delete('/cart-clear', function (Request $request, Response $response) {
    $conn = $GLOBALS['connect'];
    $sql = "DELETE FROM tbl_cart";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->affected_rows;

    if ($result > 0) {
        // ลบสำเร็จ
        return $response
            ->withStatus(204); // 204 No Content
    } else {
        // ไม่พบรายการหรือมีข้อผิดพลาด
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(404);
    }
});


$app->post('/order', function (Request $request, Response $response) {
    $json = $request->getBody();
    $jsonData = json_decode($json, true);
    $conn = $GLOBALS['connect'];

    $sql = "INSERT INTO `orders`(`orders_loterry_num`, `orders_loterry_amount`, `orders_loterry_price`, `order_date`, `mem_id`) VALUES (?, ?, ?, NOW(), ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssss', $jsonData['orders_loterry_num'], $jsonData['orders_loterry_amount'], $jsonData['orders_loterry_price'], $jsonData['mem_id']);
    $stmt->execute();

    $affected = $stmt->affected_rows;
    if ($affected > 0) {
        $data = ["affected_rows" => $affected, "last_id" => $conn->insert_id];
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    } else {
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(500);
    }
});


// $app->get('/order-show/{mem_id}', function (Request $request, Response $response,$args) {
//     $conn = $GLOBALS['connect'];
//     $mem_id = $args['mem_id'];

//     $sql = 'SELECT orders.order_id, orders.orders_loterry_num, orders.orders_loterry_amount, orders.orders_loterry_price, orders.mem_id, tbl_member.firstname FROM orders INNER JOIN tbl_member ON orders.mem_id = tbl_member.mem_id WHERE orders.mem_id = ?';

//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param('s', $mem_id);
//     $stmt->execute();

//     $result = $stmt->get_result();
//     $data = array();
//     foreach ($result as $row) {
//         $data[] = $row;
//     }

//     $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
//     return $response
//         ->withHeader('Content-Type', 'application/json; charset=utf-8')
//         ->withStatus(200);
// });

$app->get('/order-show/{mem_id}', function (Request $request, Response $response,$args) {
    $conn = $GLOBALS['connect'];
    $mem_id = $args['mem_id'];

    $sql = 'SELECT orders.order_id, orders.orders_loterry_num, orders.orders_loterry_amount, orders.orders_loterry_price,orders.order_date, orders.mem_id, tbl_member.firstname, tbl_member.lastname FROM orders INNER JOIN tbl_member ON orders.mem_id = tbl_member.mem_id WHERE orders.mem_id = ?';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $mem_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $data = array();
    foreach ($result as $row) {
        $data[] = $row;
    }

    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
    return $response
        ->withHeader('Content-Type', 'application/json; charset=utf-8')
        ->withStatus(200);
});


$app->get('/today/day/{day}', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS['connect'];
    $day = $args['day'];

    $sql = 'SELECT tbl_member.firstname, tbl_member.lastname, orders.orders_loterry_num, orders.orders_loterry_amount, orders.orders_loterry_price, SUM(orders.orders_loterry_price) AS total_price_sum
            FROM tbl_member
            INNER JOIN orders ON tbl_member.mem_id = orders.mem_id
            WHERE DATE(orders.order_date) = DATE(?)
            GROUP BY tbl_member.firstname, tbl_member.lastname, orders.orders_loterry_num, orders.orders_loterry_amount, orders.orders_loterry_price;
    ';
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $day);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);

        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
        return $response->withHeader('Content-Type', 'application/json; charset=utf-8')->withStatus(200);
    } else {
        return $response->withStatus(404);
    }
});
$app->put('/admin-update/{lot_id}', function (Request $request, Response $response, $args) {
    $lot_id = $args['lot_id'];
    $conn = $GLOBALS['connect'];
    $data = json_decode($request->getBody(), true);

    $stmt = $conn->prepare("UPDATE tbl_lottery SET lot_date=?, lot_number=?, lot_no=?, set_no=?, price=?, lot_amount=? WHERE lot_id =?");
    $stmt->bind_param("ssssssi", $data['lot_date'], $data['lot_number'], $data['lot_no'], $data['set_no'], $data['price'], $data['lot_amount'], $lot_id);
    $stmt->execute();
    $result = $stmt->affected_rows;

    if ($result > 0) {
        return $response
            ->withStatus(204);
    } else {
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(404);
    }
});
$app->get('/admin-show/{lot_id}', function (Request $request, Response $response, $args) {
    $lot_id = $args['lot_id'];
    $conn = $GLOBALS['connect'];
    $stmt = $conn->prepare("SELECT * FROM tbl_lottery WHERE lot_id = ?");
    $stmt->bind_param("i", $lot_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $response->getBody()->write(json_encode($row, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus(200);
    } else {
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(404);
    }
});

$app->get('/today/all', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS['connect'];

    $sql = 'SELECT tbl_member.firstname, tbl_member.lastname, orders.orders_loterry_num, orders.orders_loterry_amount, orders.orders_loterry_price, orders.orders_loterry_price AS total_price_sum
            FROM tbl_member
            INNER JOIN orders ON tbl_member.mem_id = orders.mem_id
    ';
    
    $stmt = $conn->prepare($sql);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);

        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
        return $response->withHeader('Content-Type', 'application/json; charset=utf-8')->withStatus(200);
    } else {
        return $response->withStatus(404);
    }
});