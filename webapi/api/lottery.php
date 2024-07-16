<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request; 

    $app->get('/lottery', function (Request $request, Response $response) {
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
    $app->post('/lottery/search', function (Request $request, Response $response, $args) {
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
?>