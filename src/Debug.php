<?php
namespace InternalFunctionsDebug;

/**
 * Template function for exec method - Debug::exec with hanlder function name
 */
function template(...$args) {
    $trace = debug_backtrace();
    return Debug::exec($trace[1]['function'], $args);
}

/**
 * Class for debugging internal php functions
 *
 * Copyright 2020 neovav. All rights reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License
 *
 * @author Verveda Aleksandr
 * @email neovav@outlook.com
 */
class Debug
{
    const BEFOR = 0;
    const AFTER = 1;

        /** @var array $handler     List handlers for internal functions */
    private static array $handler = [];

        /** @var array $listFunctionNamespaces     List functions with namespaces */
    private static array $listFunctionNamespaces = [];

    /**
     * Setup handler for global function
     *
     * @param string $name
     * @param callable $handler
     * @param bool $isBefor
     */
    public static function setHandler(string $name, callable $handler, bool $isBefor = true)
    {
        $type = ($isBefor) ? self::BEFOR : self::AFTER;
        if (empty(self::$handler[$type])) {
            self::$handler[$type] = [];
        }

        $position = mb_strrpos($name, '\\');
        if ($position !== false) {
            $name = mb_substr($name, $position + 1);
        }

        if (empty(self::$handler[$type][$name])) {
            self::$handler[$type][$name] = [];
        }
        self::$handler[$type][$name][] = $handler;
    }

    /**
     * Create namespace with global function and setup function handler
     *
     * @param string $name
     * @param callable $handler
     * @param bool $isBefor
     */
    public static function evalHandler(string $name, callable $handler, bool $isBefor = true)
    {
        $position = mb_strrpos($name, '\\');
        if ($position !== false) {
            $nameFunction = mb_substr($name, $position + 1);
            if (empty(self::$listFunctionNamespaces[$name])) {
                $namespace = mb_substr($name, 0, $position);
                $codeEval = 'namespace ' . $namespace . ' {
                    use InternalFunctionsDebug\Debug;
                    function ' . $nameFunction . '(...$args) {
                        return Debug::exec(__FUNCTION__, $args);
                    };
                };';
                self::$listFunctionNamespaces[$name] = true;
                eval($codeEval);
            }
        }
        self::setHandler($name, $handler, $isBefor);
    }

    /**
     * Execute internal functions and functions handlers
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public static function exec(string $name, array $arguments)
    {
        $position = mb_strrpos($name, '\\');
        if ($position !== false) {
            $name = mb_substr($name, $position + 1);
        }
        if (!empty(self::$handler[self::BEFOR][$name])) {
            $count = count(self::$handler[self::BEFOR][$name]);
            for ($i = 0; $i < $count; $i++) {
                $func = self::$handler[self::BEFOR][$name][$i];
                $func($name, $arguments);
            }
        }
        $result = call_user_func_array('\\'.$name, $arguments);
        if (!empty(self::$handler[self::AFTER][$name])) {
            $count = count(self::$handler[self::AFTER][$name]);
            for ($i = 0; $i < $count; $i++) {
                $func = self::$handler[self::AFTER][$name][$i];
                $func($name, $arguments, $result);
            }
        }
        return $result;
    }
}