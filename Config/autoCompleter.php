<?php
// -- 存放自动补全相关代码
function getDoc($title, $params = [], $return = 'mix', $description = '')
{
    $paramsStr = '';
    foreach ($params as $param) $paramsStr .= '@param '.$param.PHP_EOL;
    return <<<EOF
{$title}<hr>{$paramsStr}<hr>@return {$return}
EOF;
}

return [
    // php 标识的自动补全(可以定义自己的key) 只做展示用
    'php' => [
        // 关键字
        'yaoyaoaijiaojiao',

        // 有文档的注释
        'Yconfig::get($name);' => getDoc('获取配置', ['$name string 名称'], 'string'),
    ]
];