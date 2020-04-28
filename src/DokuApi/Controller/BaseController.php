<?php
namespace Burdock\DokuApi\Controller;

use PDO;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class BaseController
{
    protected static $pdo = null;
    protected static $logger = null;

    public static function setPDOInstance(PDO $pdo): void
    {
        static::$pdo = $pdo;
    }

    public static function getPDOInstance(): PDO
    {
        return static::$pdo;
    }

    public static function setLogger(LoggerInterface $logger): void
    {
        static::$logger = $logger;
    }

    public static function getLogger(): LoggerInterface
    {
        return is_null(static::$logger) ? new NullLogger() : static::$logger;
    }

    public static function initialize()
    {

    }
}