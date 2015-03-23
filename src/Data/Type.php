<?php

namespace Data;

final class Type
{
    // 保持原样, 啥也不干
    const ORIGIN = 'origin';

    // string类型会强制去掉标签，并对html进行转义，不对引号转义
    const STRING = 'string';

    const BOOLEAN = 'boolean';

    const INT = 'int';

    const FLOAT = 'float';

    const ARR = 'array';

    // html类型会对html进行转义，注意html decode
    const HTML = 'html';

    const EMAIL = 'email';

    const IP = 'ip';

    const URL = 'url';

    const POI = 'poi';

    const PHONE = 'phone';

    const FN = 'function';

    const JSONP = 'jsonp';

    // 经纬度不合格时，会返回false
    const LATITUDE = 'latitude';

    const LONGITUDE = 'longitude';

    const JSON = 'json';

    const ENUM = 'enum';
}