<?php

$default_host = '';
$default_port = '';
$default_db = '';
$default_username = '';
$default_password = '';
$default_prefix = '';

$default_slave_db = '';
$default_slave_host = '';
$default_slave_username = '';
$default_slave_password = '';
$default_slave_port = '';

$redis_host = '127.0.0.1';
$redis_password = NULL;
$redis_port = '6379';
$redis_database = '0';
$redis_cache_database = '1';

$mongo_host = '127.0.0.1';
$mongo_username = '';
$mongo_password = '';
$mongo_port = '27017';
$mongo_database = '';


$db_conn_name = env('DB_CONNECTION', 'mysql');

if (env('APP_Framework', false) != 'platform') {
    include dirname(dirname(dirname(__DIR__))) . '/data/config.php';
} else {
    if (file_exists(base_path('database/config.php'))) {
        include base_path('database/config.php');
    }

    if (file_exists(base_path('database/redis.php'))) {
        include base_path('database/redis.php');

        if (!empty($redis)) {
            $redis_host = $redis['host'] ?: $redis_host;
            $redis_password = $redis['password'] ?: $redis_password;
            $redis_port = $redis['port'] ?: $redis_port;
            $redis_database = $redis['database'] ?: $redis_database;
            $redis_cache_database = $redis['cache_database'] ?: $redis_cache_database;
        }
    }

}

if (file_exists(base_path('database/mongoDB.php'))) {
    include base_path('database/mongoDB.php');

    if (!empty($mongoDB)) {
        $mongo_host = $mongoDB['host'] ?: $mongo_host;
        $mongo_username = $mongoDB['username'] ?: $mongo_username;
        $mongo_password = $mongoDB['password'] ?: $mongo_password;
        $mongo_port = $mongoDB['port'] ?: $mongo_port;
        $mongo_database = $mongoDB['database'] ?: $mongo_database;
    }
}

if (isset($config)) {
    if (isset($config['db']['master'])) {
        $default_host = $config['db']['master']['host'];
        $default_port = $config['db']['master']['port'];
        $default_db = $config['db']['master']['database'];
        $default_username = $config['db']['master']['username'];
        $default_password = $config['db']['master']['password'];
        $default_prefix = $config['db']['master']['tablepre'];
        if(isset($config['db']['slave'][1])){
            $default_slave_db = $config['db']['slave'][1]['database'];
            $default_slave_host = $config['db']['slave'][1]['host'];
            $default_slave_username = $config['db']['slave'][1]['username'];
            $default_slave_password = $config['db']['slave'][1]['password'];
            $default_slave_port = $config['db']['slave'][1]['port'];
        }

    } else {
        $default_host = $config['db']['host'];
        $default_port = $config['db']['port'];
        $default_db = $config['db']['database'];
        $default_username = $config['db']['username'];
        $default_password = $config['db']['password'];
        $default_prefix = $config['db']['tablepre'];
    }

    if (!empty($config['setting']['redis'])) {
        $redis_host = isset($config['setting']['redis']['server']) ? $config['setting']['redis']['server'] : $redis_host ;
        $redis_password =!empty($config['setting']['redis']['auth']) ? $config['setting']['redis']['auth'] : $redis_password;
        $redis_port = isset($config['setting']['redis']['port']) ? $config['setting']['redis']['port'] : $redis_port;
        $redis_database = isset($config['setting']['redis']['database']) ? $config['setting']['redis']['database'] : $redis_database;
        $redis_cache_database = isset($config['setting']['redis']['cache']) ? $config['setting']['redis']['cache'] : $redis_cache_database;
    }

    if (!empty($config['setting']['mongodb'])) {
        $mongo_host = isset($config['setting']['mongodb']['server']) ? $config['setting']['mongodb']['server'] : $mongo_host ;
        $mongo_port =!empty($config['setting']['mongodb']['port']) ? $config['setting']['mongodb']['port'] : $mongo_port;
        $mongo_database = isset($config['setting']['mongodb']['database']) ? $config['setting']['mongodb']['database'] : $mongo_database;
        $mongo_username = isset($config['setting']['mongodb']['username']) ? $config['setting']['mongodb']['username'] : $mongo_username;
        $mongo_password = isset($config['setting']['mongodb']['password']) ? $config['setting']['mongodb']['password'] : $mongo_password;
    }

}


return [

    /**
     * 数据查询结果集返回模式
     */
    'fetch' => \PDO::FETCH_ASSOC,

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => $db_conn_name,

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
        ],

        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', $default_host),
            'port' => env('DB_PORT', $default_port),
            'database' => env('DB_DATABASE', $default_db),
            'username' => env('DB_USERNAME', $default_username),
            'password' => env('DB_PASSWORD', $default_password),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => env('DB_PREFIX', $default_prefix),
            'strict' => false,
            'engine' => null,
            'loggingQueries' => true,
            'options' => [
                \PDO::ATTR_EMULATE_PREPARES => env('DB_PREPARED', false)
            ]

        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'mysql_slave' => [
            'driver' => 'mysql',
            'write' => [
                'host' => env('DB_HOST', $default_host),
            ],
            'read' => [
                'host' => env('DB_SLAVE_HOST', $default_slave_host),
            ],
            'port' => env('DB_PORT', $default_port),
            'database' => env('DB_DATABASE', $default_db),
            'username' => env('DB_USERNAME', $default_username),
            'password' => env('DB_PASSWORD', $default_password),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => env('DB_PREFIX', $default_prefix),
            'strict' => false,
            'engine' => null,
            'loggingQueries' => true,

        ],


        'mongodb' => [         //MongoDB
            'driver'   => 'mongodb',
            'host'     => env('MONGO_HOST', $mongo_host),
            'port'     => env('MONGO_PORT', $mongo_port),
            'database' => env('MONGO_DATABASE', empty($mongo_database)?$default_db:$mongo_database),
            'username' => env('MONGO_USERNAME', $mongo_username),
            'password' => env('MONGO_PASSWORD', $mongo_password)
        ]

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => 'predis',

        'default' => [
            'host' => env('REDIS_HOST', $redis_host),
            'password' => env('REDIS_PASSWORD', $redis_password),
            'port' => env('REDIS_PORT', $redis_port),
            'database' => env('REDIS_DEFAULT_DATABASE', $redis_database)
        ],
        'cache' => [
            'host' => env('REDIS_HOST', $redis_host),
            'password' => env('REDIS_PASSWORD', $redis_password),
            'port' => env('REDIS_PORT', $redis_port),
            'database' => env('REDIS_CACHE_DATABASE', $redis_cache_database)
        ]
    ],

];