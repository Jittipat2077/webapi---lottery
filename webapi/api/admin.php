<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/lottery_dashboard', function (Request $request, Response $response) {
    $conn = $GLOBALS['connect'];
    $sql = 'SELECT * FROM tbl_lottery';
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

$app->delete('/lottery_delete/{lot_id}', function (Request $request, Response $response, $args) {
    $lot_id = $args['lot_id']; // รับค่า id จาก URL
    $conn = $GLOBALS['connect'];
    $stmt = $conn->prepare("DELETE FROM tbl_lottery WHERE lot_id=?");
    $stmt->bind_param("s", $lot_id);
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

$app->post('/admin-lottery/search', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS['connect'];
    $requestData = json_decode($request->getBody(), true);
    $lot_number = $requestData['lot_number'];
    $set_no = $requestData['set_no'];
    $lot_no = $requestData['lot_no'];
    
    $sql = 'SELECT * FROM tbl_lottery WHERE SUBSTRING(lot_number, -6) = ? OR SUBSTRING(lot_number, -5) = ? OR SUBSTRING(lot_number, -4) = ? OR SUBSTRING(lot_number, -3) = ? OR SUBSTRING(lot_number, -2) = ? OR SUBSTRING(lot_number, -1) = ? OR set_no = ? OR lot_no = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssssss', $lot_number,$lot_number,$lot_number,$lot_number,$lot_number,$lot_number,$set_no,$lot_no);
    
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

$app->post('/add-lottery', function (Request $request, Response $response, $args) {
    $jsonData = $request->getParsedBody();
    $conn = $GLOBALS['connect'];

    $sql = "INSERT INTO `tbl_lottery` (`lot_date`, `lot_number`, `lot_no`, `set_no`, `price`, `lot_amount`) VALUES (?, ?, ?, ?, ?, ?)";

    $lot_date = date("Y-m-d", strtotime($jsonData['lot_date']));

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssss', $lot_date, $jsonData['lot_number'], $jsonData['lot_no'], $jsonData['set_no'], $jsonData['price'], $jsonData['lot_amount']);
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


$app->get('/orders-show', function (Request $request, Response $response) {
    $conn = $GLOBALS['connect'];
    $sql = 'SELECT * FROM `orders`';
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

$app->get('/dashboard/all', function (Request $request, Response $response, $args) {
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


$app->get('/dashboard/day/{day}', function (Request $request, Response $response, $args) {
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

$app->get('/dashboard/month/all', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS['connect'];

    $sql = 'SELECT tbl_member.firstname, tbl_member.lastname, orders.orders_loterry_num, orders.orders_loterry_amount, orders.orders_loterry_price,orders.orders_loterry_price AS total_price_sum
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

$app->get('/dashboard/month/{month}', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS['connect'];
    $month = $args['month'];

    $sql = 'SELECT tbl_member.firstname, tbl_member.lastname, orders.orders_loterry_num, orders.orders_loterry_amount, orders.orders_loterry_price, SUM(orders.orders_loterry_price) AS total_price_sum
    FROM tbl_member
    INNER JOIN orders ON tbl_member.mem_id = orders.mem_id
    WHERE MONTH(orders.order_date) = ?
    GROUP BY tbl_member.firstname, tbl_member.lastname, orders.orders_loterry_num, orders.orders_loterry_amount, orders.orders_loterry_price;
';
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $month);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);

        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
        return $response->withHeader('Content-Type', 'application/json; charset=utf-8')->withStatus(200);
    } else {
        return $response->withStatus(404);
    }
});



// $app->get('/dashboard/month/{month}', function (Request $request, Response $response, $args) {
//     $conn = $GLOBALS['connect'];
//     $month = $args['month'];


//     $sql = 'SELECT tbl_member.firstname, tbl_member.lastname, orders.orders_loterry_num, orders.orders_loterry_amount, orders.orders_loterry_price, SUM(orders.orders_loterry_price) AS total_price_sum
//     FROM tbl_member
//     INNER JOIN orders ON tbl_member.mem_id = orders.mem_id
//     WHERE MONTH(orders.order_date) = DATE(?)
//     GROUP BY tbl_member.firstname, tbl_member.lastname, orders.orders_loterry_num, orders.orders_loterry_amount, orders.orders_loterry_price;
// ';

//     // $sql = 'SELECT account.fname, account.lname, lottory_ticket.ticket_id, detail_buy_ticket.amount, detail_buy_ticket.total_price
//     // FROM buy_ticket 
//     // INNER JOIN detail_buy_ticket ON buy_ticket.buy_id = detail_buy_ticket.buy_id 
//     // INNER JOIN lottory_ticket ON lottory_ticket.lot_id = detail_buy_ticket.lot_id
//     // INNER JOIN account ON account.ac_id = buy_ticket.ac_id
//     // WHERE MONTH(buy_ticket.buy_date) = ?';

//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param('s', $month);

//     if ($stmt->execute()) {
//         $result = $stmt->get_result();
//         $data = array();
//         foreach ($result as $row) {
//             array_push($data, $row);
//         }

//         // คำนวณรวมของคอลัมน์ total_price
//         $totalPriceSum = 0;
//         foreach ($data as $row) {
//             $totalPriceSum += $row['total_price'];
//         }

//         // เพิ่มคอลัมน์ total_price_sum ในผลลัพธ์
//         $data[0]['total_price_sum'] = $totalPriceSum;

//         $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
//         return $response->withHeader('Content-Type', 'application/json; charset=utf-8')->withStatus(200);
//     } else {
//         return $response->withStatus(404);
//     }
// });
// $app->post('/add-lottery', function (Request $request, Response $response, $args) {
//     $json = $request->getBody();
//     $jsonData = json_decode($json, true);
//     $conn = $GLOBALS['connect'];

//     $sql = "INSERT INTO `tbl_lottery` (`lot_date`, `lot_number`, `lot_no`, `set_no`, `price`, `lot_amount`) VALUES (?, ?, ?, ?, ?, ?)";

//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param('ssssss', $jsonData['lot_date'], $jsonData['lot_number'], $jsonData['lot_no'], $jsonData['set_no'], $jsonData['price'], $jsonData['lot_amount']);
//     $stmt->execute();

//     $affected = $stmt->affected_rows;
//     if ($affected > 0) {
//         $data = ["affected_rows" => $affected, "last_id" => $conn->insert_id];
//         $response->getBody()->write(json_encode($data));
//         return $response
//             ->withHeader('Content-Type', 'application/json')
//             ->withStatus(200);
//     } else {
//         // Insertion failed
//         return $response
//             ->withHeader('Content-Type', 'application/json')
//             ->withStatus(500);
//     }
// });
