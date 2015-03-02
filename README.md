# verify
数据验证器

## Usage:

```
use Data\Verify as D;
use Data\Type as DT;
```

1. D::verify, 提供各类验证器
```
   D::verify($_GET, 'ip', DT::IP)
```
2. D::get, 提供获取器
```
   D::get($apiData, 'poi.name'), 将获得 $apiData['poi']['name']
```

3. D::pipe, 提供冒泡获取器
```
   D::pipe($_GET, 'name|username'), 如果$_GET没有name这个key，将取 $_GET['username']
```

4. D::getCols, 提供列获取器

5. D::hashMap, 根据某列转化为关联数组
