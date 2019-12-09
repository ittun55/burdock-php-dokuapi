<?php
namespace Burdock\DokuApi\Controller;

use Burdock\Config\Config;
use PDO;
use Psr\Log\LoggerInterface;

class BaseController
{
    protected static $config = null;
    protected static $logger = null;
    protected static $pdo    = null;

    public static function setConfig(Config $config)
    {
        static::$config = $config;
    }

    public static function setLogger(LoggerInterface $logger)
    {
        static::$logger = $logger;
    }

    public static function setPdo(PDO $pdo)
    {
        static::$pdo = $pdo;
    }
}