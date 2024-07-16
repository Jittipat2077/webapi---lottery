<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// $app->post('/login', function (Request $request, Response $response, $args) {
//     $json = $request->getBody();
//     $loginData = json_decode($json, true);
//     $conn = $GLOBALS['connect'];

//     $email = $loginData['email'];
//     $password = $loginData['password'];

//     $sql = "SELECT mem_id, email, password, firstname FROM tbl_member WHERE email = ?";
//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param('s', $email);
//     $stmt->execute();
//     $stmt->store_result();

//     if ($stmt->num_rows === 1) {
//         $stmt->bind_result($id, $dbEmail, $dbPassword);
//         $stmt->fetch();
//         if (password_verify($password, $dbPassword)) {
//             // Password is correct
//             $data = ["id" => $id];
//             $response->getBody()->write(json_encode($data));
//             return $response
//                 ->withHeader('Content-Type', 'application/json')
//                 ->withStatus(200);
//         } else {
//             // Incorrect password
//             return $response
//                 ->withStatus(401); // Unauthorized
//         }
//     } else {
//         // User not found
//         return $response
//             ->withStatus(401); // Unauthorized
//     }
// });



$app->post('/login', function (Request $request, Response $response, $args) {
    $json = $request->getBody();
    $jsonData = json_decode($json, true);
    $email = $jsonData['email'];
    $password = $jsonData['password'];

    $conn = $GLOBALS['connect'];
    $sql = 'SELECT * FROM tbl_member WHERE email = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $role = $user['role'];

            if ($role === 'user' || $role === 'admin') {
                $data = ["message" => "เข้าสู่ระบบสำเร็จ", "user" => $user, "redirect" => $role === 'admin' ? "dashboard" : "main"];
                $response->getBody()->write(json_encode($data));
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(200);
            } else {
                $data = ["message" => "ไม่พบข้อมูล"];
            }

            $response->getBody()->write(json_encode($data));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } else {
            $data = ["message" => "อีเมลหรือรหัสผ่านไม่ถูกต้อง"];
            $response->getBody()->write(json_encode($data));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
        }
    } else {
        $data = ["message" => "ไม่พบข้อมูล"];
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }
});
?>