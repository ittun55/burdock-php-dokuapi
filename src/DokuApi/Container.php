<?php
namespace Burdock\DokuApi;

use Burdock\Config;
use Exception;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use PDO;
use Pimple\Container as Pimple;
use Psr\Log\LoggerInterface;

class Container
{
    protected static $container;

    public static function initialize()
    {
        self::$container = new Pimple();
        self::$container['config'] = function($c) {
            return Config::load(DOKU_INC . 'api/config.json');
        };
        self::$container['pdo'] = function($c) {
            return self::createPdo($c['config']);
        };
        self::$container['logger'] = function($c) {
            return self::createLogger($c['config']);
        };
        self::$container['schema'] = function($c) {
            $config = $c['config'];
            $schemaConfig = Config::load(DOKU_INC . $config->getValue('db.schema'));
            return $schemaConfig->getValue();
        };
    }

    public static function get($service)
    {
        return self::$container[$service];
    }

    /**
     * DEBUG     => 100
     * INFO      => 200
     * NOTICE    => 250
     * WARNING   => 300
     * ERROR     => 400
     * CRITICAL  => 500
     * ALERT     => 550
     * EMERGENCY => 600
     * @param Config $config
     * @return LoggerInterface
     * @throws Exception
     */
    public static function createLogger(Config $config): LoggerInterface
    {
        $logger = new Logger($config->getValue('logger.name'));
        $path   = $config->getValue('logger.path');
        $count  = $config->getValue('logger.count');
        $level  = $config->getValue('logger.level');
        $stderr = $config->getValue('logger.stderr');
        $fileHandler = new RotatingFileHandler($path, $count, $level);
        $streamHandler = new StreamHandler('php://stderr', $stderr);
        $logger->pushHandler($fileHandler);
        $logger->pushHandler($streamHandler);
        return $logger;
    }

    public static function createPdo(Config $config): PDO
    {
        $host     = $config->getValue('db.host');
        $port     = $config->getValue('db.port');
        $dbname   = $config->getValue('db.name');
        $charset  = $config->getValue('db.charset');
        $username = $config->getValue('db.user');
        $password = $config->getValue('db.pass');
        $dsn = "mysql:host=${host};port=${port};dbname=${dbname};charset=${charset}";
        $options  = array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        );
        return new PDO($dsn, $username, $password, $options);
    }
}