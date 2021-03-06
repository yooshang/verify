# verify
数据验证器

该Tool可方便验证数据源是否符合预期，并提供多种快速获取或者转换数据的方法，解决工程上不断isset empty等验证数据的麻烦
例如
```
$primary = 'test';

// 不严谨的判断会造成很多notice，代码冗长且不直观
if (isset($p['detail']) && isset($p['detail']['primary']) && $p['detail']['primary'] == $primary) {
    $url = isset($p['detail']['source'][$primary]['url']) ? $p['detail']['source'][$primary]['url'] : '';
    $p['detail']['url'] = $url;
} else {
    $p['detail']['url'] = '';
}

// 以上代码将简化
$p['detail']['url'] = D::get($p, "detail.source.{$primary}.url");

// 默认值为''的话，使用verify方法，默认DT::STRING类型
$p['detail']['url'] = D::verify($p, "detail.source.{$primary}.url");
```

# 支持校验类型
* string
* boolean
* int
* float
* array
* html
* email
* ip
* url
* phone
* jsonp
* json
* latitude 纬度
* longitude 经度
* poi 16位数字+字母的字符串
* function 回调函数
* origin 保持原样
* enum 枚举型

## Usage:

常用别名
```
use Data\Verify as D;
use Data\Type as DT;
```

* D::verify, 提供各类验证器
```
   D::verify($_GET, 'ip', DT::IP);
   // 支持正则
   D::verify($_GET, 'input', DT::STRING, 'regex=/^18\d{9}$/');
   // 支持回调
   D::verify($_GET, 'input', DT::STRING, function ($value) {
       return strtolower($value);
   });
```
* D::get, 提供获取器
```
   D::get($apiData, 'poi.name'), 将获得 $apiData['poi']['name']
```

* D::pipe, 提供冒泡获取器
```
   D::pipe($_GET, 'name|username'), 如果$_GET没有name这个key，将取 $_GET['username']
```

* D::rename, 提供替换key的方法
```
   D::rename($_GET, 'name', 'username'), 将把name这个key替换为username
```

* D::getCols, 提供列获取器

* D::hashMap, 根据某列转化为关联数组
