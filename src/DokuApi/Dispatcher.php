<?php
namespace Burdock\DokuApi;

use Exception;
use Throwable;

class Dispatcher
{
    private static $aclFunc = null;

    public static function setAclFunc($func): void
    {
        self::$aclFunc = $func;
    }

    public static function dispatch(?Array $userinfo): void
    {
        try {
            $request = self::parseRequest();

            if (is_null($request) || !isset($request['resource'])) {
                Response::error(400, ['_summary' => 'Invalid Request'], true);
            }

            $resource = $request['resource'];
            $user     = $userinfo['user'];
            $groups   = $userinfo['grps'];

            if (!self::checkAcl($resource, $user, $groups)) {
                Response::error(403, ['_summary' => 'Forbidden'], true);
            }

            $controller = self::getController($resource);
            $action = isset($request['action']) ? $request['action'] : 'index';
    
            if (!method_exists($controller, $action)) {
                Response::error(404, ['_summary' => 'Not Found'], true);
            }

            $controller::initialize();
            $params = isset($request['params']) ? $request['params'] : null;
            $controller::$action($params, $userinfo);
        } catch (Throwable $e) {
            $logger = Container::get('logger.default');
            $logger->error($e);
            Response::error(500, [
                '_summary' => $e->getMessage(),
                '_trace' => $e->getTrace()
            ], true);
        }
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
     * @throws Exception
     */
    public static function checkAcl($resource, $user, $groups): bool
    {
        $data = [
            'id' => $resource, // 名前空間
            'user' => $user, // user は環境変数から取得
            'groups' => $groups
        ];
        $func = self::$aclFunc;
        if (!is_callable($func))
            throw new Exception('Auth function is not specified');
        return ($func($data) >= 1);
    }

    public static function getController(string $resource): string
    {
        $routing = Container::get('config')->getValue('routing');
        return array_key_exists($resource, $routing) ? $routing[$resource] : $routing['default'];
    }
}