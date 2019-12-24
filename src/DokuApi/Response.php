<?php
namespace Burdock\DokuApi;


class Response
{
    public static function error($code, $errors, $setStatus=false): void
    {
        if ($setStatus) http_response_code($code);
        header('content-type: application/json; charset=utf-8');
        $err = [ 'code' => $code, 'errors' => $errors ];
        echo json_encode($err, JSON_PRETTY_PRINT);
        exit;
    }

    public static function send($code, $data, $setStatus=false): void
    {
        if ($setStatus) http_response_code($code);
        header('content-type: application/json; charset=utf-8');
        $err = [ 'code' => $code, 'data' => $data ];
        echo json_encode($err, JSON_PRETTY_PRINT);
        exit;
    }
}