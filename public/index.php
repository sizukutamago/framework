<?php
$basedir = dirname(__DIR__);

require_once $basedir . '/vendor/autoload.php';

//.envの読み込み
$dotenv = new \Dotenv\Dotenv($basedir);
$dotenv->load();

//全てのエラーを表示
error_reporting(E_ALL);

if (env('APP_ENV', 'production') === 'production') {
    //エラーを画面に表示させない
    ini_set('display_errors', 0);

    $logger = new \Monolog\Logger('SizukBBS');
    $logger->pushHandler(new \Monolog\Handler\SlackHandler(env('SLACK_TOKEN'), env('SLACK_CHANNEL'), env('SLACK_NAME'), true, null, \Monolog\Logger::DEBUG));
} else {
    $whoops = new \Whoops\Run();
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
    $whoops->register();
}

//timezoneを日本に
date_default_timezone_set(env('TIME_ZONE', 'Asia/Tokyo'));

//twigの設定
$loader = new \Twig_Loader_Filesystem($basedir . '/view');
$twig = new \Twig_Environment($loader, [
    'cache' => $basedir . '/cache/twig',
    'debug' => true,
]);

//DB接続
$db_name = env('DB_NAME');
$db_host = env('DB_HOST');
$db_port = env('DB_PORT');

ORM::configure([
    'connection_string' => "mysql:dbname=$db_name;host=$db_host:$db_port:/tmp/mysql.sock;charset=utf8mb4",
    'username' => env('DB_USER'),
    'password' => env('DB_PASSWORD', ''),
    'driver_options' => [
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'
    ]
]);

//ルーティング
$routes = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    $r->get('/', '\SizukuBBS\Controllers\TopController@index');
});

// リクエストパラメータを取得する
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// リクエストURLからクエリストリング(?foo=bar)を除去したうえで、URIデコードする
$pos = strpos($uri, '?');
if ($pos !== false) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $routes->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::FOUND:
        // ルーティングに従って処理を実行
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        list($class, $method) = explode('@', $handler, 2);
        $ref = new \ReflectionClass($class);
        $instance = $ref->newInstance($twig);
        call_user_func_array([$instance, $method], $vars);
        break;

    case FastRoute\Dispatcher::NOT_FOUND:
        // Not Foundだった時
        header('HTTP', true, 404);
        echo "404 Not Found.";
        break;

    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        // Method Not Allowedだった時
        $allowedMethods = $routeInfo[1];
        header('HTTP', true, 405);
        echo "405 Method Not Allowed.  allow only=" . json_encode($allowedMethods);
        break;

    default:
        header('HTTP', true, 500);
        echo "500 Server Error.";
        break;
}
