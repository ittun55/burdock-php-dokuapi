<?php declare(strict_types=1);
if (!defined('DOKU_INC')) define('DOKU_INC',dirname(__FILE__).'/../../');
if (!defined('NL')) define('NL',"\n");
define('DOKU_DISABLE_GZIP_OUTPUT', 1);

global $USERINFO;
require_once(DOKU_INC.'inc/init.php');
session_write_close();  //close session

$autoloader = require_once(DOKU_INC . 'api/vendor/autoload.php');
//echo json_encode($autoloader->getPrefixesPsr4(), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
//$result = $autoloader->findFile('Api\Dispatcher');
//echo json_encode($result);
//exit;

Burdock\DokuApi\Dispatcher::dispatch($USERINFO);

