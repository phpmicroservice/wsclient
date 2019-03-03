<?php
//include './logo.php';
echo "开始主程序! \n";
define("SERVICE_NAME", "wsclient");# 设置服务名字
define('ROOT_DIR', dirname(__DIR__));
require ROOT_DIR . '/vendor/autoload.php';
# 进行一些项目配置
define('APP_DEBUG', \pms\get_env("APP_DEBUG", false));
define('APP_SECRET_KEY', \pms\get_env("APP_SECRET_KEY"));
define('DI_FILE', ROOT_DIR . '/app/di.php');


$re9 = \pms\env_exist([
    'GCACHE_HOST', 'GCACHE_PORT', 'GCACHE_AUTH', 'GCACHE_PERSISTENT', 'GCACHE_PREFIX', 'GCACHE_INDEX',
    'MYSQL_HOST', 'MYSQL_PORT', 'MYSQL_DBNAME', 'MYSQL_PASSWORD', 'MYSQL_USERNAME']);
if (is_string($re9)) {
    exit('defined :' . $re9);
}
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('log_errors', 'On');
    ini_set('error_log', './phplog.log');
}

//注册自动加载
$loader = new \Phalcon\Loader();
$loader->registerNamespaces(
    [
        'apps' => ROOT_DIR . '/apps/',
        'tool' => ROOT_DIR . '/tool/',
    ]
);
$loader->register();

$server = new \pms\WsServer('0.0.0.0', 9502, SWOOLE_PROCESS, SWOOLE_SOCK_TCP, [
    'daemonize' => false,
    'reactor_num_mulriple' => 2,
    'worker_num_mulriple' => 4,
    'task_worker_num_mulriple' => 1,
    'reload_async' => false,
]);

include_once ROOT_DIR . '/app/di.php';

$guidance = new \app\Guidance();
$server->onBind('onWorkerStart', $guidance);
$server->onBind('beforeStart', $guidance);
$server->onBind('onStart', $guidance);
$server->start();
