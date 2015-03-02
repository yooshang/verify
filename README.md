# verify
数据验证器

该Tool提供verify验证器验证数据源的数据是否符合预期，并提供多种快速获取或者转换数据的方法，解决工程上不断isset empty等验证数据的烦恼。
例如
```
   if (isset($p['detail']) && isset($p['detail']['primary']) && $p['detail']['primary'] == 'test') {
      $url = isset($p['detail']['source']['test']['url']) ? $p['detail']['source']['test']['url']:'';                                                                                                               
      $p['detail']['url'] = $url;
   } else {
      $p['detail']['url'] = '';
   }
   
   // 以上代码将简化为，没有默认值
   $p['detail']['url'] = D::get($p, 'detail.source.test.url');
   
   // 或者，默认值''
   $p['detail']['url'] = D::verify($p, 'detail.source.test.url');
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

## Usage:

个人习惯的缩写
```
use Data\Verify as D;
use Data\Type as DT;
```

* D::verify, 提供各类验证器
```
   D::verify($_GET, 'ip', DT::IP)
```
* D::get, 提供获取器
```
   D::get($apiData, 'poi.name'), 将获得 $apiData['poi']['name']
```

* D::pipe, 提供冒泡获取器
```
   D::pipe($_GET, 'name|username'), 如果$_GET没有name这个key，将取 $_GET['username']
```

* D::getCols, 提供列获取器

* D::hashMap, 根据某列转化为关联数组
