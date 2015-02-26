<?php

require(dirname(__DIR__) . '/config/config.php');
require(ROOT_PATH . '/app/controller.php');


$app = new controller();

$_GET['t'] = 'a';
try{
    $t = $app->get('t', 'string', function ($t) {
        return false;
    });
    var_dump(true);
} catch (CParameterException $e) {
    var_dump(false);
}

$_POST['t'] = 'a';
try{
    $t = $app->post('t', 'string', function ($t) {
        throw new CParameterException('test');
    });
    var_dump(false);
} catch (CParameterException $e) {
    var_dump(true);
}

$_GET['t'] = '';
try{
    $t = $app->get('t', 'string', 'require,empty');
    var_dump(true);
} catch (CParameterException $e) {
    var_dump(false);
}

$_GET['t'] = '';
try{
    $t = $app->get('t1', 'string', 'require,empty');
    var_dump(false);
} catch (CParameterException $e) {
    var_dump(true);
}

$_GET['t'] = '';
var_dump('' === $app->get('t', 'string'));

$_GET['t'] = 'string';
var_dump('string' === $app->get('t', 'string'));

$_GET['t'] = 'string ';
var_dump('string' === $app->get('t', 'string'));

$_GET['t'] = ' a b ';
var_dump('a b' === $app->get('t', 'string'));

$_GET['t'] = 'string<div>2</div>"tt"';
var_dump('string2"tt"' === $app->get('t', 'string'));

$_GET['t'] = 1;
var_dump('1' === $app->get('t', 'string'));

$_GET['t'] = '';
var_dump(0 === $app->get('t', 'int'));

$_GET['t'] = '3';
var_dump(3 === $app->get('t', 'int'));

$_GET['t'] = '3.3';
var_dump(3 === $app->get('t', 'int'));

$_GET['t'] = 'string';
var_dump(0 === $app->get('t', 'int'));

$_GET['t'] = '3.3';
var_dump(3.3 === $app->get('t', 'float'));

$_GET['t'] = '<script>alert(1);</script><div>2</div>';
var_dump($_GET['t'] === htmlspecialchars_decode($app->get('t', 'html')));

$_GET['t'] = urldecode('%22%E4%BA%BA%E5%B7%A5%E6%99%B6%E4%BD%93%22%20%22%E7%99%BD%E5%86%85%E9%9A%9C%22');
$t = $app->get('t');
var_dump($t == '"人工晶体" "白内障"');

$_GET['t'] = ['<script>alert(1);</script><div>2</div>', '3.3', 3];
$t = $app->get('t', 'array');
var_dump(is_array($t) && $t[0] === '<script>alert(1);</script><div>2</div>' && $t[1] === '3.3' && $t[2] == 3);



$_GET['t'] = '';
try{
    $t = $app->get('t', 'string', 'require');
} catch (CParameterException $e) {
    var_dump(true);
}

$_GET['t'] = '';
try{
    $t = $app->get('t', 'string', 'require', 'wrong');
} catch (CParameterException $e) {
    var_dump($e->getMessage() === 'wrong');
}

$_GET['t'] = '';
try{
    $t = $app->get('t', 'string', 'require', ['require' => 'wrong']);
} catch (CParameterException $e) {
    var_dump($e->getMessage() === 'wrong');
}

$_GET['t'] = '';
var_dump('aa' === $app->get('t', 'string', 'default=aa'));

$_GET['t'] = '';
var_dump('aa' === $app->get('t1', 'string', 'default=aa'));

$_GET['t'] = 0;
var_dump(10 === $app->get('t', 'int', 'default=10'));

$_GET['t'] = '18520880070';
var_dump('18520880070' === $app->get('t', 'string', 'regex=/^18\d{9}$/'));

$_GET['t'] = '18520880070';
var_dump('18520880070' === $app->get('t', 'string', ['regex' => '/^18\d{9}$/']));

$_GET['t'] = '1852088007d';
try{
    $t = $app->get('t', 'string', 'require,regex=/^18\d{9}$/');
    var_dump(false);
} catch (CParameterException $e) {
    var_dump(true);
}

$_GET['t'] = '';
try{
    $t = $app->get('t', 'string', 'require,regex=/^18\d{9}$/');
    var_dump(false);
} catch (CParameterException $e) {
    var_dump(true);
}

$_GET['t'] = '1852088007d';
try{
    $t = $app->get('t', 'string', ['require' => true, 'regex' => '/^18\d{9}$/']);
    var_dump(false);
} catch (CParameterException $e) {
    var_dump(true);
}

$_GET['t'] = 'a';
$t = $app->get('t', 'string', function ($t) {
        return $t . 'b';
    });
var_dump($t === 'ab');

$_GET['t'] = 'a';
$t = $app->get('t', 'string', function ($t) {
        return '';
    });
var_dump($t === '');


$_POST['t'] = ' a b ';
var_dump('a b' === $app->post('t', 'string'));

$_REQUEST['t'] = ' a b ';
var_dump('a b' === $app->req('t', 'string'));

/*** 扫描出来的漏洞 ***/
$_GET['t'] = ['a' => 1];
var_dump("" === $app->get('t', 'string'));

$_GET['t'] = new StdClass();
var_dump("" === $app->get('t', 'string'));

/*** 新增的类型 ***/
$_GET['t'] = '10.16.29.11';
var_dump('10.16.29.11' === $app->get('t', 'ip'));

$_GET['t'] = '10.16.29.1111';
var_dump('' === $app->get('t', 'ip'));

$_GET['t'] = 'haosou.com';
var_dump('' === $app->get('t', 'url'));

$_GET['t'] = 'http://haosou.com';
var_dump('http://haosou.com' === $app->get('t', 'url'));

$_GET['t'] = 'http://sea2.ms.com/app/?c=city&city_id=haosou.com&showkw=1';
var_dump($_GET['t'] === $app->get('t', 'url'));

$_GET['t'] = 'fulianjie@360.cn';
var_dump('fulianjie@360.cn' === $app->get('t', 'email'));

$_GET['t'] = 'fulianjie@360.';
var_dump('' === $app->get('t', 'email'));

$_GET['t'] = 'fulianjie';
var_dump('' === $app->get('t', 'email'));

$_GET['t'] = '8cc9f9433d309740';
var_dump('8cc9f9433d309740' === $app->get('t', 'poi'));

$_GET['t'] = '8cc9f9433d309740.';
var_dump('' === $app->get('t', 'poi'));

$_GET['t'] = '8cc9f9433d30974.';
var_dump('' === $app->get('t', 'poi'));

// 手机检测, +86等暂不处理
$_GET['t'] = '18520880070';
var_dump('18520880070' === $app->get('t', 'phone'));

$_GET['t'] = '11082448877';
var_dump('' === $app->get('t', 'phone'));

$_GET['t'] = '82448877';
var_dump('' === $app->get('t', 'phone'));

// 增加min max
$_GET['t'] = '2';
var_dump(3 === $app->get('t', 'int', 'min=3'));

$_GET['t'] = '2.1';
var_dump(3 === $app->get('t', 'float', 'min=3'));

$_GET['t'] = '2';
var_dump('2' === $app->get('t', 'string', 'min=3'));

$_GET['t'] = '2';
var_dump(1 === $app->get('t', 'int', 'max=1'));

$_GET['t'] = '2.1';
var_dump(1 === $app->get('t', 'int', 'max=1'));

// 别的类别无效
$_GET['t'] = '2';
var_dump('2' === $app->get('t', 'string', 'max=1'));

$_GET['t'] = function(){};
var_dump($app->get('t', 'function') instanceof Closure);

$_GET['t'] = 'jQuery18309860740937292576_1415974289446';
var_dump('jQuery18309860740937292576_1415974289446' === $app->get('t', 'jsonp'));

$_GET['t'] = 'jQuery18309860740937292576_<1415974289446';
var_dump('jQuery18309860740937292576_' === $app->get('t', 'jsonp'));

$_GET['t'] = '1<img src=1 onerror=alert(42873)>';
var_dump('1' === $app->get('t', 'jsonp'));

$_GET['t'] = '1%3Cimg%20src=1%20onerror=alert%2842873%29%3E';
var_dump('13Cimg20src120onerroralert2842873293E' === $app->get('t', 'jsonp'));

// 经纬度
$_GET['t'] = '12.3';
var_dump(12.3 === $app->get('t', 'longitude'));

$_GET['t'] = '-180.3';
var_dump(false === $app->get('t', 'longitude'));

$_GET['t'] = '1.3';
var_dump(1.3 === $app->get('t', 'latitude'));

$_GET['t'] = '91.3';
var_dump(false === $app->get('t', 'latitude'));

$_GET['t'] = '91.3';
try {
    var_dump($app->get('t', 'latitude', 'require'));
    var_dump(false);
} catch (CParameterException $e) {
    var_dump(true);
}

// json
$_GET['t'] = '{"a":1}';
$t = $app->get('t', Data\TYPE::JSON);
var_dump($t['a'] === 1);

$_GET['t'] = "{'a':1}";
$t = $app->get('t', Data\TYPE::JSON);
var_dump(json_encode($t) == '[]' );

$_GET['t'] = "xxx";
$t = $app->get('t', Data\TYPE::JSON);
var_dump(json_encode($t) == '[]' );


$_GET['t'] = 'sdfsf<script>alert(1);</script>';
$t = $app->get('t', Data\TYPE::ORIGIN);
var_dump('sdfsf<script>alert(1);</script>' == $t);

$_GET['t'] = ['a' => '1'];
$t = $app->get('t', Data\TYPE::ORIGIN);
var_dump($t['a'] === '1');

$_GET['t'] = null;
$t = $app->get('t', Data\TYPE::ORIGIN);
var_dump($t === null);

$_GET['t'] = null;
try{
$t = $app->get('t', Data\TYPE::ORIGIN, 'require');
var_dump(false);
} catch (CParameterException $e) {
var_dump(true);
}