<?php
namespace Burdock\DokuApi;

use Burdock\Config\Config;
use Exception;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use PDO;
use Psr\Log\LoggerInterface;

class Dispatcher
{
    private static $config = null;
    private static $logger = null;
    private static $pdo    = null;

    public static function dispatch(?Array $userinfo): void
    {
        try {
            self::loadServices();

            $request  = self::parseRequest();
            if (is_null($request)) {
                header('content-type: application/json; charset=utf-8');
                $err = [ 'code' => 400, 'errors' => [
                    '_summary' => 'Invalid Request'
                ]];
                echo json_encode($err, JSON_PRETTY_PRINT);
                exit;
            }
            $resource = $request['resource'];
            $user     = $_SERVER['REMOTE_USER'];
            $groups   = $userinfo['grps'];

            if (self::checkAcl($resource, $user, $groups)) {
                $controller = self::getController($resource);
                $action     = isset($request['action']) ? $request['action'] : 'index';
                $controller::setConfig(self::$config);
                $controller::setLogger(self::$logger);
                $controller::setPdo(self::$pdo);
                $controller::$action();
            } else {
                header('content-type: application/json; charset=utf-8');
                $err = [ 'code' => 403, 'errors' => [
                    '_summary' => 'Forbidden'
                ]];
                echo json_encode($err, JSON_PRETTY_PRINT);
                exit;
            }
        } catch (Exception $e) {

        }
    }

    /**
     * @throws Exception
     */
    public static function loadServices(): void
    {
        self::$config = Config::load(DOKU_INC . 'api/config.json');
        self::$pdo    = self::createPdo(self::$config);
        self::$logger = self::createLogger(self::$config);
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
        $path = __DIR__.'/'.$config->getValue('logger.path');
        $count = $config->getValue('logger.count');
        $level = $config->getValue('logger.level');
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

    public static function parseRequest(): ?array
    {
        // リクエストボディをデコード
        $content_type = explode(';', trim(strtolower($_SERVER['CONTENT_TYPE'])));
        $media_type = $content_type[0];

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $media_type == 'application/json') {
            // application/json で送信されてきた場合の処理
            return json_decode(file_get_contents('php://input'), true);
        } elseif ($_SERVER['REQUEST_METHOD'] == 'PUT' && $media_type == 'application/json') {
            // application/json で送信されてきた場合の処理
            return json_decode(file_get_contents('php://input'), true);
        } else {
            return null;
        }
    }

    /**
     * inc/auth.php に定義がある
     * 0 => 'AUTH_NONE',
     * 1 => 'AUTH_READ',
     * 2 => 'AUTH_EDIT',
     * 4 => 'AUTH_CREATE',
     * 8 => 'AUTH_UPLOAD',
     * 16 => 'AUTH_DELETE',
     * 255 => 'AUTH_ADMIN'
     * @param $resource
     * @param $user
     * @param $groups
     * @return bool
     */
    public static function checkAcl($resource, $user, $groups): bool
    {
        $data = [
            'id' => $resource, // 名前空間
            'user' => $user, // user は環境変数から取得
            'groups' => $groups
        ];
        return (auth_aclcheck_cb($data) >= 1);
    }

    public static function getController(string $resource): string
    {
        $routing = self::$config->getValue('routing');
        return array_key_exists($resource, $routing) ? $routing[$resource] : $routing['default'];
    }
}

/*
$res = [
    'user' => [
        'name' => $USERINFO['name'],
        'mail' => $USERINFO['mail'],
    ],
    'group'      => $USERINFO['grps'],
    'request' => [
        'resource'   => $data['id'],
        'method'     => 'get | post | put | delete | options',
        'action'     => 'if needed',
        'body'       => 'json contents'
    ],
    'permission' => $permissions[$permission],
    'response' => [
        'code'   => 200,
        'errors' => [
            '_summary' => 'errors for not specific model fields',
            'field-1'  => 'unique index error',
        ],
        'data' => [
            'items' => [
                ['id' => 0],
                ['id' => 1],
                ['id' => 2],
                ['id' => 3]
            ]
        ]
    ]
];

header('content-type: application/json; charset=utf-8');
echo json_encode($res, JSON_PRETTY_PRINT);
*/