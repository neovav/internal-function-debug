<?php
namespace Test;

use InternalFunctionsDebug\Debug;

function abs(...$args) {return Debug::exec(__FUNCTION__, $args);}

class DebugTest extends \PHPUnit\Framework\TestCase
{
    protected $fixture;

    public static array $args = [];

    public function setUp(): void
    {
        $class = new \ReflectionClass('InternalFunctionsDebug\Debug');
        $this->fixture = $class->getProperty('handler');
        $this->fixture->setAccessible(true);
    }

    public function tearDown(): void
    {
        $this->fixture->setValue([]);
        DebugTest::$args = [];
    }

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
        Debug::setHandler($name, $handler, $isBefor);
        $this->assertEquals($result, $this->fixture->getValue());
    }

    public function providerSetHandler() {
        $handler0 = function($name, $arguments) {};
        $handler1 = function($name, $arguments, $result) {};
        return [
            ['abs', $handler0, true, ['befor' => ['abs' => [$handler0]]]],
            ['abs', $handler0, false, ['after' => ['abs' => [$handler1]]]]
        ];
    }

    /**
     * @var string $name
     * @var callable $handler
     * @var int $number
     * @var array $result
     *
     * @dataProvider providerExecBefor
     *
     * @throws
     */
    public function testExecBefor(string $name, callable $handler, int $number, array $result)
    {
        Debug::setHandler($name, $handler, true);
        abs($number);
        $this->assertEquals($result, DebugTest::$args);
    }

    public function providerExecBefor() {
        $handler0 = function($name, $arguments) {
            DebugTest::$args = func_get_args();
        };
        return [
            ['abs', $handler0, -7, ['abs', [-7]]],
            ['abs', $handler0, 7, ['abs', [7]]],
            ['abs', $handler0, -1, ['abs', [-1]]],
            ['abs', $handler0, 0, ['abs', [0]]],
        ];
    }

    /**
     * @var string $name
     * @var callable $handler
     * @var int $number
     * @var array $result
     *
     * @dataProvider providerExecAfter
     *
     * @throws
     */
    public function testExecAfter(string $name, callable $handler, int $number, array $result)
    {
        Debug::setHandler($name, $handler, false);
        abs($number);
        $this->assertEquals($result, DebugTest::$args);
    }

    public function providerExecAfter() {
        $handler0 = function($name, $arguments, $result) {
            DebugTest::$args = func_get_args();
        };
        return [
            ['abs', $handler0, -7, ['abs', [-7], 7]],
            ['abs', $handler0, 7, ['abs', [7], 7]],
            ['abs', $handler0, -1, ['abs', [-1], 1]],
            ['abs', $handler0, 0, ['abs', [0], 0]],
        ];
    }
}