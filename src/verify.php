<?php

namespace Data;

/**
 * 数据验证dataValid
 */
class Verify
{
    /**
     * 模式符号
     * $是统配整个数组所有的下标，返回值会自动拼接成数组
     * @example
     *  $test = [['a' => 1], ['a' => 2]], get($test, '$.a') = [1,2]
     */
    const PATTERN_SYMBOL = '$';

    /**
     * 保留字
     */
    private static $reserved = ['require', 'empty'];

    /**
     * 验证【数据源】的某一项是否指定类型
     * @params {Array} $datas 数据集
     * @params {String} $key 下标
     * @params {Enum} $type 类型，可枚举
     * @params {Mix} $paras 参数，设定项
     *        require: 不能为空，否则【抛出异常】
     *        empty: 允许为空
     *        default: 默认值，如果$paras不包含'='或','，且不是保留字，则认为是默认项
     *        min: 最小值
     *        max: 最大值
     * @params {Mix|Array|String} $error 出错后的提示内容
     * @example
     *   Data\Verify::verify($_GET, 'page', DATA\TYPE::INT, 'default=1,min=1,max=10,require', '请输入page参数')
     *   Data\Verify::verify($_GET, 'page', DATA\TYPE::INT, function ($page) {
             return max(1, $page);
         });
     *   Data\Verify::verify($_GET, 'phone', DATA\TYPE::STRING, 'regex=/^18\d{9}$/');
     */
    public static function verify($datas, $key, $type = TYPE::STRING, $paras = [], $error = [])
    {
        // 解析参数
        $paras = self::parseParas($paras);

        if ($paras instanceof \Closure) {
            return self::doFn($paras, self::get($datas, $key));
        }

        $value = null;

        // 如果是原始类型并且没有设置require, 则默认允许为空
        if (TYPE::ORIGIN == $type && empty($paras['require'])) {
            $paras['empty'] = true;
        }

        $value = self::get($datas, $key);

        if (!is_null($value)) {
            // 如果为空且require
            if (!empty($paras['require']) && empty($value) && empty($paras['empty'])) {
                return self::_doError($type, $paras, $error);
            }
        } else {
            // 如果不存在key，则require，则处理错误
            if (!empty($paras['require'])) {
                return self::_doError($type, $paras, $error);
            }
        }

        // 根据类型进行修正
        $value = self::correctTypeValue($value, $type, $paras);
        $value = self::_check($value, $type, $paras, $error);

        return $value;
    }

    /**
     * 直接验证值
     * require和empty不可共用，require优先级高
     */
    public static function vv($value, $type = TYPE::STRING, $paras = [], $error = [])
    {
        // 解析参数
        $paras = self::parseParas($paras);

        if ($paras instanceof \Closure) {
            return self::doFn($paras, $value);
        }

        // 如果为空且require
        if (!empty($paras['require']) && empty($value)) {
            return self::_doError($type, $paras, $error);
        }

        // 如果是原始类型并且没有设置require, 则默认允许为空
        if (TYPE::ORIGIN == $type && empty($paras['require'])) {
            $paras['empty'] = true;
        }

        // 根据类型进行修正
        $value = self::correctTypeValue($value, $type, $paras);
        $value = self::_check($value, $type, $paras, $error);

        return $value;
    }

    /**
     * 路径取值
     * @notice 没有必要处理 $key === '$' 的情况 - -
     */
    public static function get($datas, $key)
    {
        $keys = explode('.', $key);

        if (count($keys) === 1) {
            return isset($datas[$key]) ? $datas[$key] : null;
        }

        $index = $datas;
        // 如果不包含通配符
        if (strpos($key, self::PATTERN_SYMBOL) === false) {
            foreach ($keys as $k) {
                if (isset($index[$k])) {
                    $index = $index[$k];
                } else {
                    return null;
                }
            }

            return $index;
        }

        return self::getPattern($datas, $keys);
    }

    /**
     * 串式获得数据
     * 只有非null才会停止
     */
    public static function bunch($datas, $keys)
    {
        if (!is_array($keys)) {
            $keys = explode('|', $keys);
        }

        if (empty($keys)) {
            return null;
        }

        foreach ($keys as $key) {
            $value = self::get($datas, $key);

            if (is_null($value)) {
                continue;
            }

            return $value;
        }
    }

    /**
     * get的对应方法set
     * @example
     *   $t = ['a' => 2];
     *   $data = \Data\Verify::set($t, 'b.c', 3);
     *   $t['b']['c'] = 3;
     */
    public static function set(&$data, $key, $value)
    {
        $keys = explode('.', $key);

        if (count($keys) === 1) {
            $data[$key] = $value;
            return $data;
        }

        $mark = &$data;
        foreach ($keys as $key) {
            $mark = &$mark[$key];
        }

        $mark = $value;

        return $data;
    }

    /**
     * 模式处理
     */
    private static function getPattern($datas, $keys)
    {
        // 标记可用数据
        $index = $datas;
        $max = count($keys) - 1;
        $i = 0;
        $values = [];

        while ($i <= $max && $index) {
            $key = $keys[$i];

            if ($key == self::PATTERN_SYMBOL) {
                // $结尾,保持原样
                if ($i != $max) {
                    $index = self::getCols($index, $keys[++$i]);
                }
            } else {
                $index = isset($index[$key]) ? $index[$key] : null;
            }

            $i++;
        }

        $values = $index;
        return $values;
    }

    /**
     * 解析参数
     * @example require,default=1,empty=1
     */
    public static function parseParas($paras)
    {
        if (is_array($paras) || $paras instanceof \Closure) {
            return $paras;
        }

        $paras = explode(',', $paras);

        // 只有一个值
        // 不是保留关键词
        // 且不含=, 则认为是默认值
        if (count($paras) == 1) {
            if (strpos($paras[0], '=') === false && !in_array($paras[0], self::$reserved)) {
                return ['default' => $paras[0]];
            }
        }

        $r = [];

        foreach ($paras as $p) {
            $p = explode('=', $p);

            $r[$p[0]] = isset($p[1]) ? $p[1] : 1;
        }

        return $r;
    }

    /**
     * 出错处理
     */
    protected static function _doError($type, $paras = [], $error = [])
    {
        //如果必须有此参数
        if (isset($paras['require'])) {
            //抛出异常
            throw new Exception(self::getError($error, 'require'));
        }

        if (isset($paras['default'])) {
            return self::correctTypeValue($paras['default'], $type, $paras);
        }

        $defaults = [
            TYPE::INT => 0,
            TYPE::FLOAT => 0,
            TYPE::ARR => [],
            TYPE::LATITUDE => false,
            TYPE::LONGITUDE => false,
            TYPE::JSON => []
        ];

        if (isset($defaults[$type])) {
            return $defaults[$type];
        } else {
            return '';
        }

        return null;
    }

    /**
     * 执行闭包函数
     * 函数自己处理异常
     */
    private static function doFn($closure, $value)
    {
        $value = $closure($value);

        return $value;
    }

    protected static function _check($value, $type, $paras = [], $error = [])
    {
        // 布尔型false是ok的
        if ($value === false && $type === TYPE::BOOLEAN) {
            return $value;
        }

        // 如果允许为空
        if (!empty($paras['empty']) && empty($value)) {
            return $value;
        }

        if (isset($paras['regex'])) {
            if (filter_var($value, FILTER_VALIDATE_REGEXP, [
                   'options' => ['regexp' => $paras['regex']]
               ])
            ) {
                return $value;
            } else {
                $value = '';
            }
        }

        if (empty($value)) {
            return self::_doError($type, $paras, $error);
        }

        return $value;
    }

    /**
     * 检测值的正确性
     */
    protected static function correctTypeValue($value, $type, $paras = [])
    {
        //根据类型进行
        switch ($type) {
            case TYPE::ORIGIN:
                break;
            case TYPE::STRING:
                if (is_string($value) || is_numeric($value)) {
                    $value = strval(self::filterString($value));
                } else {
                    $value = '';
                }
                break;
            case TYPE::BOOLEAN:
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                break;
            case TYPE::INT:
                $value = intval($value);
                break;
            case TYPE::FLOAT:
                $value = floatval($value);
                break;
            case TYPE::ARR:
                if (!is_array($value)) {
                    $value = [];
                }
                // $value = self::filterString($value);
                break;
            case TYPE::HTML:
                $value = self::filterString($value, 1);
                break;
            case TYPE::EMAIL:
                $value = filter_var($value, FILTER_VALIDATE_EMAIL);
                break;
            case TYPE::IP:
                $value = filter_var($value, FILTER_VALIDATE_IP);
                break;
            case TYPE::URL:
                $value = filter_var($value, FILTER_VALIDATE_URL);
                break;
            case TYPE::POI:
                $value = self::isPoiID($value) ? $value : '';
                break;
            case TYPE::PHONE:
                $value = self::isMobilePhone($value) ? $value : '';
                break;
            case TYPE::FN:
                $value = $value instanceof \Closure ? $value : null;
                break;
            case TYPE::JSONP:
                $value = is_string($value) ? preg_replace('/[^0-9a-zA-Z_\.\[\]]/', '', self::filterString($value)) : '';
                break;
            case TYPE::LATITUDE:
                $value = floatval($value);

                if (abs($value) > 90 || $value == 0) {
                    $value = null;
                }

                break;
            case TYPE::LONGITUDE:
                $value = floatval($value);

                if (abs($value) > 180 || $value == 0) {
                    $value = null;
                }

                break;
            case TYPE::JSON:
                $value = json_decode($value, true);
                break;
            default:
                $value = '';
                break;
        }

        if (in_array($type, [TYPE::INT, TYPE::FLOAT]) && !empty($paras)) {
            $value = self::_minMax($value, $paras);
        }

        return $value;
    }

    protected static function _minMax($value, $options = [])
    {
        if (isset($options['min'])) {
            $value = max($value, intval($options['min']));
        }

        if (isset($options['max'])) {
            $value = min($value, intval($options['max']));
        }

        return $value;
    }

    /**
     * 过滤字符串
     * 默认的string会过滤掉html标签，并进行html转义
     * 即使指定了html，也会进行html转义，使用时看情况decode
     * @notice 引号不转义
     * @params {string} $string 被过滤字符串
     * @params {Boolean} $is_html 是否要保留html标签 @default 0
     */
    protected static function filterString($string, $is_html = 0)
    {
        if (! is_array($string)) {
            if (is_string($string)) {
                if ($is_html) {
                    return htmlspecialchars(trim($string), ENT_NOQUOTES);
                } else {
                    return htmlspecialchars(strip_tags(trim($string)), ENT_NOQUOTES);
                }
            } else {
                return $string;
            }
        } else {
            foreach ($string as & $s) {
                $s = self::filterString($s, $is_html);
            }

            return $string;
        }
    }

    protected static function getError($error, $key = '')
    {
        if (is_string($error)) {
            return $error;
        }

        if (is_array($error) && isset($error[$key])) {
            return $error[$key];
        }

        return 'error';
    }

    /**
     * 验证是否一个poi的id
     * 规则为16位的字符串
     */
    public static function isPoiID($pid)
    {
        return preg_match('/^[A-Za-z0-9]{16}$/', $pid) ? true : false;
    }

    /**
     * 验证是否手机号
     * @todo 够用，但并不严谨
     * @param {string} $phone 手机号
     * @return Boolean
     */
    public static function isMobilePhone($phone)
    {
        return preg_match('/^(18|17|13|14|15)\d{9}$/', $phone) ? true : false;
    }


    public static function getCols($arr, $col)
    {
        if (empty($arr)) {
            return [];
        }

        $ret = [];
        foreach ($arr as $row) {
            if (isset($row[$col])) {
                $ret[] = $row[$col];
            }
        }

        return $ret;
    }

    public static function to_hashmap($arr, $key)
    {
        return self::hashMap($arr, $key);
    }

    public static function hashMap($arr, $key)
    {
        $r = [];

        foreach ($arr as $k => $v) {
            if (isset($v[$key])) {
                $r[$v[$key]] = $v;
            }
        }

        return $r;
    }
}