<?php
namespace Burdock\DokuApi\Controller;

class NotFoundController extends BaseController
{
    public static function index()
    {
        header('content-type: application/json; charset=utf-8');
        $err = [ 'code' => 404, 'errors' => [
            '_summary' => 'Not Found'
        ]];
        echo json_encode($err, JSON_PRETTY_PRINT);
    }
}