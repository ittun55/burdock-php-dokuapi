<?php
namespace Burdock\DokuApi\Controller;

class BaseController
{
    public static function initialize()
    {

    }

    public static function sendErrorResponse($code, $errors): void
    {
        header('content-type: application/json; charset=utf-8');
        $err = [ 'code' => $code, 'errors' => $errors ];
        echo json_encode($err, JSON_PRETTY_PRINT);
        exit;
    }
}