<?php
use InternalFunctionsDebug\Debug;

class DebugTest extends PHPUnit\Framework\TestCase
{
    /**
     * @var string $name
     * @var callable $handler
     * @var bool $isBefor
     * @var array $result
     *
     * @dataProvider providerSetHandler
     *
     * @throws
     */
    public function testSetHandler(string $name, callable $handler, bool $isBefor, array $result)
    {
        $class = new ReflectionClass('InternalFunctionsDebug\Debug');
        $property = $class->getProperty('handler');
        $property->setAccessible(true);
        Debug::setHandler($name, $handler, $isBefor);
        $this->assertEquals($result, $property->getValue());
    }

    public function providerSetHandler() {
        $handler0 = function($name, $arguments) {};
        $handler1 = function($name, $arguments, $result) {};
        return [
            ['abs', $handler0, true, ['befor' => ['abs' => [$handler0]]]],
            ['abs', $handler0, false, ['befor' => ['abs' => [$handler0]], 'after' => ['abs' => [$handler1]]]]
        ];
    }
}