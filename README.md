# 萤石云开放平台PHP SDK  

代码Demo

    <?php
    
    use Delz\Ys7\Client;
    use Redis as PHPRedis;
    use Delz\Cache\Provider\Redis;
    
    $loader = require __DIR__ . "/../vendor/autoload.php";
    
    $redis = new PHPRedis();
    $redis->connect('127.0.0.1',6379);
    $cache = new Redis($redis);
    
    $ys7 = new Client('ApiKey','ApiSecret', $cache);
    echo "AccessToken=".$ys7->getAccessToken() . "\n";
    echo "Camera List:\n";
    print_r($ys7->getCameraList());