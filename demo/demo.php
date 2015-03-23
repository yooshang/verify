<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Data\Verify as D;
use Data\Type as DT;

$demo = [
    'a' => 1,
    'b' => [
        'c' => '010',
        'd' => [
            ['id' => 1],
            ['id' => 2],
            ['id' => 181]
        ]
    ],
];

// 1
var_dump(D::get($demo, 'a'));

// '010'
var_dump(D::get($demo, 'b.c'));

// '1'
var_dump(D::get($demo, 'b.d.0.id'));

// NULL
var_dump(D::get($demo, 'CanNotFind'));

// '010'
var_dump(D::pipe($demo, 'CanNotFind|a.c|b.c'));

// [1, 2, 181]
var_dump(D::getCols(D::get($demo, 'b.d'), 'id'));

// [
//       1 => ['id' => 1],
//       2 => ['id' => 2],
//     181 => ['id' => 181],
// ]
var_dump(D::hashMap(D::get($demo, 'b.d'), 'id'));

// 5
var_dump(D::verify($demo, 'page', DT::INT, 'default=5,min=1,max=10'));

// false
var_dump(D::verify($demo, 'b.d.e', DT::LONGITUDE));

// '北京'
var_dump(D::verify($demo, 'b.c', DT::STRING, function ($value) {
            if ($value == '010') {
                return '北京';
            }
        }
    )
);

// 1
var_dump(D::verify($demo, 'a', DT::ENUM, ['enums' => [1, 5, 10], 'default' => 10]));

// '010' doesn't match this pattern, so give default string(0) back
var_dump(D::verify($demo, 'b.c', DT::STRING, 'regex=/^\d{4}$/'));

// require('login').wrongId()
try {
    // error msg can be ERROR_NUMBER too
    $id = D::verify($demo, 'b.c', DT::STRING, ['regex' => '/^\d{4}$/', 'require' => true], "require('login').wrongId()");
} catch (\Data\Exception $e) {
    var_dump($e->getMessage());
}

// rename
var_dump(D::rename($demo, 'b.c', 'b.e'));