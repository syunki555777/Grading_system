<?php
    //該当のjavascriptからの返答であるかどうか、公開鍵と秘密鍵で確認(ssl)
    $key = "";
    // Check if request is a POST request
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = file_get_contents('php://input');

        // Do something with $data

        // Send response
        header('Content-Type: application/json');
        echo json_encode(['status' => 'OK']);
    } else {
        // Not a POST request, handle accordingly
        header('HTTP/1.0 405 Method Not Allowed');
    }
    ?>