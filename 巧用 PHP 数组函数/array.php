<?php

//巧用 PHP 数组函数


/*
取指定键名
*/
$raw = ['id' => 1, 'name' => 'zane', 'password' => '123456'];
function newOnlyKeys($array, $keys) {
    return array_intersect_key($array, array_flip($keys));
}
var_dump(onlyKeys($raw, ['id', 'name']));
// 结果 ['id' => 1, 'name' => 'zane']
var_dump(newOnlyKeys($raw, ['id', 'name']));
// 结果 ['id' => 1, 'name' => 'zane']



/*
移除指定键名
*/
function removeKeys($array, $keys) {
    return array_diff_key($array, array_flip($keys));
}
// 移除 id 键
var_dump(removeKeys($raw, ['id', 'password']));
// 结果 ['name' => 'zane']



/*
重置索引
*/
$input = [0 => 233, 99 => 666];
var_dump(array_values($input));
// 结果 [0 => 233, 1 => 66]



/**
获取指定键名之前 / 之后的数组
*/
function afterKey($array, $key) {
    $keys = array_keys($array);
    $offset = array_search($key, $keys);
    return array_slice($array, $offset + 1);
}
var_dump(afterKey($data, 'first'));
// 结果 ['second' => 2, 'third' => 3]
var_dump(afterKey($data, 'second'));
// 结果 ['third' => 3]
var_dump(afterKey($data, 'third'));
// 结果 []


