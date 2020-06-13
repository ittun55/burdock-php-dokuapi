<?php
namespace Burdock\DokuApi;

use Burdock\Config\Config;
use Burdock\DataModel\Model;
use Burdock\DokuApi\Controller\BaseController;
use Burdock\SendMail\SendMail;
use Exception;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use PDO;
use Pimple\Container as Pimple;
use Psr\Log\LoggerInterface;

class Container
{
    protected static $container;

    public static function initialize(string $config_path): void
    {
        self::$container = new Pimple();
        self::$container['config'] = function($c) use ($config_path) {
            return Config::load($config_path);
        };

        $root_dir = dirname(realpath($config_path));
        $config = self::$container['config'];
        $config->setValue('app.root_dir', $root_dir);
        if (php_sapi_name() !== 'cli') {
            $base_url = (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"];
            $config->setValue('app.base_url', $base_url);
        }

        self::initPdo($config->getValue('db'), self::$container);
        self::initLogger($config->getValue('logger'), self::$container);
        self::initMailer($config->getValue('smtp'), self::$container);
        self::initModel(self::$container);
        self::initController(self::$container);

        self::$container['schema'] = function($c) {
            $config = $c['config'];
            $schema_path = $config->getValue('app.root_dir').'/Model/schema.json';
            $schemaConfig = Config::load($schema_path);
            return $schemaConfig->getValue();
        };
    }

    public static function get($service)
    {
        return self::$container[$service];
    }

    public static function set($service, $callable)
    {
        self::$container[$service] = $callable;
    }

    public static function initPdo($db, $container)
    {
        foreach($db as $conn => $setting) {
            $container['pdo.'.$conn] = self::createPdo($setting);
        }
    }

    public static function createPdo(array $setting): PDO
    {
        $host     = $setting['host'];
        $port     = $setting['port'];
        $dbname   = $setting['name'];
        $charset  = $setting['charset'];
        $username = $setting['user'];
        $password = $setting['pass'];
        $dsn = "mysql:host=${host};port=${port};dbname=${dbname};charset=${charset}";
        $options  = array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false
        );
        return new PDO($dsn, $username, $password, $options);
    }

    public static function initLogger($logger, $container)
    {
        foreach($logger as $name => $setting) {
            $container['logger.'.$name] = self::createLogger($name, $setting);
        }
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
     * @param string $name
     * @param array $setting
     * @return LoggerInterface
     * @throws Exception
     */
    public static function createLogger(string $name, array $setting): LoggerInterface
    {
        $logger = new Logger($name);
        $path   = $setting['path'];
        $rotate  = $setting['rotate'];
        $level  = $setting['level'];
        $fileHandler = new RotatingFileHandler($path, $rotate, $level);
        $formatter = new LineFormatter();
        $formatter->includeStacktraces(true);
        $logger->pushHandler($fileHandler->setFormatter($formatter));
        if (isset($setting['stderr'])) {
            $stderr = $setting['stderr'];
            $streamHandler = new StreamHandler('php://stderr', $stderr);
            $logger->pushHandler($streamHandler);
        }
        return $logger;
    }

    public static function initMailer($smtp, $container): void
    {
        foreach($smtp as $name => $setting) {
            $container['mailer.'.$name] = function() use ($setting) {
                $user = $setting['user'];
                $pass = $setting['pass'];
                $host = $setting['host'];
                $port = $setting['port'];
                $dsn = "smtp://${user}:${pass}@${host}:${port}";
                return new SendMail($dsn);
            };
        }
    }

    public static function initModel($container): void
    {
        Model::setPDOInstance($container['pdo.default']);
        Model::setLogger($container['logger.default']);
    }

    public static function initController($container): void
    {
        BaseController::setPDOInstance($container['pdo.default']);
        BaseController::setLogger($container['logger.default']);
    }
}