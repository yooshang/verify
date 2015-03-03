<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Data\Verify as D;
use Data\Type as DT;

class VerifyTest extends PHPUnit_Framework_TestCase
{
    protected $stack;

    public function setUp()
    {
        $this->stack = [
            'string' => 'string',
            'empty' => '',
            'rightSpace' => 'string ',
            'lrSpace' => ' a b ',
            'htmlString' => 'string<div>2</div>"tt"',
            'scriptString' => '<script>alert(1);</script><div>2</div>',
            'encodeString' => urldecode('%22%E4%BA%BA%E5%B7%A5%E6%99%B6%E4%BD%93%22%20%22%E7%99%BD%E5%86%85%E9%9A%9C%22'),
            'int' => 1,
            'intString' => '3',
            'intZero' => 0,
            'float' => 3.3,
            'floatString' => '3.3',
            'phone' => '18520880000',
            'wrongPhone' => '18520880000d',
            'array' => ['a' => 1],
            'array2' => ['<script>alert(1);</script><div>2</div>', '3.3', 3],
            'stdClass' => new StdClass(),
            'ip' => '10.16.29.11',
            'ipWrong' => '10.16.29.1111',
            'url' => 'g.cn',
            'httpUrl' => 'http://g.cn',
            'httpsUrl' => 'https://g.cn',
            'url2' => 'http://g.cn/app/?c=city&city_id=test&test2=xx',
            'email' => 'flj10@163.com',
            'emailWrong' => 'flj10@163.',
            'emailWrong2' => 'flj10',
            'poi' => '8cc9f9433d309740',
            'poi2' => '8cc9f9433d309740.',
            'poi3' => '8cc9f9433d30974.',
            'phone' => '18588880000',
            'phone2' => '11082448877',
            'phone3' => '82448877',
            'phone4' => '1858888000d',
            'fn' => function(){},
            'jsonp' => 'jQuery18309860740937292576_1415974289446',
            'jsonp2' => 'jQuery18309860740937292576_<1415974289446',
            'jsonp3' => '1<img src=1 onerror=alert(42873)>',
            'jsonp4' => '1%3Cimg%20src=1%20onerror=alert%2842873%29%3E',
            'longitude' => '12.3',
            'longitude2' => '-180.3',
            // 0 is not valid degree in china, so ~~
            'longitude3' => 0,
            'latitude' => 1.3,
            'latitude2' => 91.3,
            'latitude3' => 0,
            'json' => '{"a":1}',
            'json2' => "{'a':1}",
            'json3' => "xxx",
            'origin' => 'sdfsf<script>alert(1);</script>',
            'origin2' => ['a' => '1'],
            'origin3' => null,
        ];
    }

    /**
     * @dataProvider simpleVerifyProvider
     */
    public function testSimpleVerify($key, $type, $expected, $params = [])
    {
        $this->assertSame(D::verify($this->stack, $key, $type, $params), $expected);
    }

    public function simpleVerifyProvider()
    {
        return [
            ['string', DT::STRING, 'string'],
            ['empty', DT::STRING, ''],
            ['rightSpace', DT::STRING, 'string'],
            ['lrSpace', DT::STRING, 'a b'],
            ['htmlString', DT::STRING, 'string2"tt"'],
            ['int', DT::STRING, '1'],
            ['int', DT::INT, 1],
            ['float', DT::INT, 3],
            ['string', DT::INT, 0],
            ['empty', DT::INT, 0],
            ['intString', DT::INT, 3],
            ['floatString', DT::INT, 3],
            ['floatString', DT::FLOAT, 3.3],
            ['floatString', DT::STRING, '3.3'],
            ['encodeString', DT::STRING, '"人工晶体" "白内障"'],
            // HTML不strip_tags，不过一定会html转义
            ['scriptString', DT::HTML, htmlspecialchars('<script>alert(1);</script><div>2</div>', ENT_NOQUOTES)],
            ['array2', DT::ARR, ['<script>alert(1);</script><div>2</div>', '3.3', 3]],
            ['array', DT::STRING, ''],
            ['stdClass', DT::STRING, ''],
            ['ip', DT::IP, '10.16.29.11'],
            ['ipWrong', DT::IP, ''],
            ['url', DT::URL, ''],
            ['httpUrl', DT::URL, 'http://g.cn'],
            ['httpsUrl', DT::URL, 'https://g.cn'],
            ['url2', DT::URL, 'http://g.cn/app/?c=city&city_id=test&test2=xx'],
            ['email', DT::EMAIL, 'flj10@163.com'],
            ['emailWrong', DT::EMAIL, ''],
            ['emailWrong2', DT::EMAIL, ''],
            ['poi', DT::POI, '8cc9f9433d309740'],
            ['poi2', DT::POI, ''],
            ['poi3', DT::POI, ''],
            ['phone', DT::PHONE, '18588880000'],
            ['phone2', DT::PHONE, ''],
            ['phone3', DT::PHONE, ''],
            ['jsonp', DT::JSONP, 'jQuery18309860740937292576_1415974289446'],
            ['jsonp2', DT::JSONP, 'jQuery18309860740937292576_'],
            ['jsonp3', DT::JSONP, '1'],
            ['jsonp4', DT::JSONP, '13Cimg20src120onerroralert2842873293E'],
            ['longitude', DT::LONGITUDE, 12.3],
            ['longitude2', DT::LONGITUDE, false],
            ['longitude3', DT::LONGITUDE, false],
            ['latitude', DT::LATITUDE, 1.3],
            ['latitude2', DT::LATITUDE, false],
            ['latitude3', DT::LATITUDE, false],
            ['json', DT::JSON, ['a' => 1]],
            ['json2', DT::JSON, []],
            ['json2', DT::JSON, []],
            ['origin', DT::ORIGIN, 'sdfsf<script>alert(1);</script>'],
            ['origin2', DT::ORIGIN, ['a' => '1']],
            ['origin3', DT::ORIGIN, null],
            ['empty', DT::STRING, 'aa', 'default=aa'],
            ['nonExist', DT::STRING, 'aa', 'default=aa'],
            ['intZero', DT::INT, 10, 'default=10'],
            ['intZero', DT::INT, 0, 'empty,default=10'],
            ['empty', DT::INT, 10, ['default' => '10']],
            ['phone', DT::STRING, '18588880000', 'regex=/^18\d{9}$/'],
            ['phone', DT::STRING, '18588880000', ['regex' => '/^18\d{9}$/']],
            ['string', DT::STRING, 'stringb', function ($t) {
                    return $t . 'b';
                }
            ],
            ['empty', DT::STRING, '', function ($t) {
                    return '';
                }
            ],
            ['int', DT::INT, 3, 'min=3'],
            ['float', DT::FLOAT, 4, 'min=4'],
            ['int', DT::STRING, '1', 'min=3'],
            ['int', DT::INT, -1, 'max=-1'],
            ['float', DT::INT, 1, ['max' => 1]],
            ['int', DT::STRING, '1', 'max=-1'],
        ];
    }

    /**
     * @dataProvider exceptionProvider
     */
    public function testVerifyException($key, $type, $params, $logicBool = true, $exceptBool = false)
    {
        $boolean = false;
        try {
            D::verify($this->stack, $key, $type, $params);
            $boolean = $logicBool;
        } catch (\Data\Exception $e) {
            $boolean = $exceptBool;
        }

        $this->assertTrue($boolean);
    }

    public function exceptionProvider()
    {
        return [
            ['string', DT::STRING, function ($t) {
                    return false;
                }
            ],
            ['string', DT::STRING, function ($t) {
                    throw new \Data\Exception('test');
                }, false, true
            ],
            ['empty', DT::STRING, 'require,empty'],
            ['nonExist', DT::STRING, 'require,empty', false, true],
            ['empty', DT::STRING, 'require', false, true],
            ['phone4', DT::STRING, 'require,regex=/^18\d{9}$/', false, true],
            ['empty', DT::STRING, 'require,regex=/^18\d{9}$/', false, true],
            ['phone4', DT::STRING, ['require' => true, 'regex' => '/^18\d{9}$/'], false, true],
            ['latitude2', DT::LATITUDE, 'require', false, true],
            ['origin3', DT::ORIGIN, ['require' => true], false, true]
        ];
    }

    /**
     * 测试函数型验证器是否正常工作
     */
    public function testFnVerify()
    {
        $this->assertTrue(D::verify($this->stack, 'fn', DT::FN) instanceof Closure);
    }

    /**
     * 测试异常抛出后的错误信息是否符合预期
     */
    public function testVerifyErrorMsg()
    {
        try {
            $t = D::verify($this->stack, 'empty', DT::STRING, 'require', 'wrong');
            $this->assertTrue(false);
        } catch (\Exception $e) {
            $this->assertSame($e->getMessage(), 'wrong');
        }

        try {
            $t = D::verify($this->stack, 'empty', DT::STRING, 'require', ['require' => 'wrong2']);
            $this->assertTrue(false);
        } catch (\Exception $e) {
            $this->assertSame($e->getMessage(), 'wrong2');
        }
    }

    /**
     * 测试D::set功能
     */
    public function testSet()
    {
        $test = ['a' => 2];

        $data = D::set($test, 'b', 3);
        $this->assertTrue($data['b'] === 3);

        $test = ['a' => 2];
        $data = D::set($test, 'b.c', 3);
        $this->assertTrue(D::get($data, 'b.c') === 3);

        $test = ['a' => 2];
        $data = D::set($test, 'b.4', 3);
        $this->assertTrue(D::get($data, 'b.4') === 3);

        $test = ['a' => 2];
        $data = \Data\Verify::set($test, 'b.4', ['c' => 5]);
        $this->assertTrue(D::get($data, 'b.4.c') === 5);
    }

    /**
     * 测试D::get功能
     */
    public function testGet()
    {
        $this->assertSame(D::get($this->stack, 'float'), 3.3);
        $this->assertSame(D::get($this->stack, 'array.a'), 1);
        $this->assertTrue(D::get($this->stack, 'array.a') !== '1');
        $this->assertSame(D::get($this->stack, 'origin3'), null);
        $this->assertSame(D::get($this->stack, 'scriptString'), '<script>alert(1);</script><div>2</div>');

        $test = [
            'a' => [
                'a1' => 1,
                'b1' => 3,
                'c1' => [
                    'a2' => 4
                ]
            ],
            'b' => [
                'a1' => 2
            ]
        ];

        $this->assertSame(D::get($test, 'a.c1'), ['a2' => 4]);
        $this->assertSame(D::get($test, '$.a1'), [1, 2]);
    }

    public function testPipe()
    {
        $test = [
            'a' => [
                'a1' => 1,
                'b1' => 3,
                'c1' => [
                    'a2' => 4
                ],
                'c2' => '',
                'c3' => 0,
                'c4' => false,
                'c5' => null
            ],
            'b' => [
                'a1' => 2
            ],
            'c' => 5
        ];

        $this->assertSame(D::pipe($test, 'c'), 5);

        $this->assertSame(D::pipe($test, 'c.a|a.c100|b.a1'), 2);

        $this->assertSame(D::pipe($test, 'a.c100|a.c1'), ['a2' => 4]);
        $this->assertSame(D::pipe($test, 'a.c100|a.c1', false), ['a2' => 4]);
        $this->assertSame(D::pipe($test, ['a.c100', 'a.c1'], false), ['a2' => 4]);

        $this->assertSame(D::pipe($test, 'a.c2|a.c1'), '');
        $this->assertSame(D::pipe($test, 'a.c2|a.c1', false), ['a2' => 4]);
        $this->assertSame(D::pipe($test, ['a.c2', 'a.c1'], false), ['a2' => 4]);

        $this->assertSame(D::pipe($test, 'a.c3|a.c1'), 0);
        $this->assertSame(D::pipe($test, 'a.c3|a.c1', false), ['a2' => 4]);

        $this->assertSame(D::pipe($test, 'a.c4|a.c1'), false);
        $this->assertSame(D::pipe($test, 'a.c4|a.c1', false), ['a2' => 4]);

        $this->assertSame(D::pipe($test, 'a.c5|a.c1'), ['a2' => 4]);
        $this->assertSame(D::pipe($test, 'a.c5|a.c1', false), ['a2' => 4]);
    }

    public function testUnset()
    {
        $test = [
            'a' => 1,
            'c' => 5
        ];

        $this->assertSame(D::remove($test, 'c'), ['a' => 1]);
        // $test already change
        $this->assertSame($test, ['a' => 1]);
        // nonexist key, do nothing
        $this->assertSame(D::remove($test, 'c.1'), ['a' => 1]);
    }

    public function testRename()
    {
        $test = [
            'a' => 1,
            'c' => 5
        ];

        $this->assertSame(D::rename($test, 'c', 'b'), ['a' => 1, 'b' => 5]);
        $this->assertSame(D::rename($test, 'b', 'c.d'), ['a' => 1, 'c' => ['d' => 5]]);
        // b has a value already, do nothing
        //$this->assertSame(D::rename($test, 'a', 'a.e'), ['a' => 1, 'c' => ['d' => 5]]);
        $this->assertSame(D::rename($test, 'a', 'c.1.d'), ['c' => ['d' => 5, 1 => ['d' => 1]]]);
        $test = [
            'a' => 1
        ];
        $this->assertSame(D::rename($test, 'a', 'a.b'), ['a' => ['b' => 1]]);
    }
}