<?php

require(dirname(__DIR__) . '/config/config.php');
require(ROOT_PATH . '/app/data/verify.php');

$t = ['a' => 2];
$data = \Data\Verify::set($t, 'b', 3);
dump($data['b'] === 3);

$t = ['a' => 2];
$data = \Data\Verify::set($t, 'b.c', 3);
dump($data['b']['c'] === 3);

$t = ['a' => 2];
$data = \Data\Verify::set($t, 'b.4', 3);
dump($data['b'][4] === 3);

$t = ['a' => 2];
$data = \Data\Verify::set($t, 'b.4', ['c' => 5]);
dump($data['b'][4]['c'] === 5);