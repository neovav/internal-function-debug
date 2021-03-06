<?php
namespace Test;

use InternalFunctionsDebug\Debug;
use function InternalFunctionsDebug\template as abs;

mb_internal_encoding("UTF-8");

require __DIR__ . '/../vendor/autoload.php';

class TestFunctionAbs
{
    public static function abs(int $number)
    {
        return abs($number);
    }
}

Debug::setHandler('abs', function($name, $arguments, $result) {
    echo "function name:\r\n";
    var_dump($name);
    echo "function arguments:\r\n";
    var_dump($arguments);
    echo "function result:\r\n";
    var_dump($result);
    echo "\r\n";
}, false);

TestFunctionAbs::abs(-7);