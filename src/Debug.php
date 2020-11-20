<?php
namespace InternalFunctionsDebug;

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
        /** @var array $handler     List handlers for internal functions */
    private static array $handler = [];

    /**
     * @param string $name
     * @param callable $handler
     * @param bool $isBefor
     */
    public static function setHandler(string $name, callable $handler, bool $isBefor = true)
    {
        $type = ($isBefor) ? 'befor' : 'after';
        if (empty(self::$handler[$type])) {
            self::$handler[$type] = [];
        }

        if (empty(self::$handler[$type][$name])) {
            self::$handler[$type][$name] = [];
        }
        self::$handler[$type][$name][] = $handler;
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
        if (!empty(self::$handler['befor'][$name])) {
            $count = count(self::$handler['befor'][$name]);
            for ($i = 0; $i < $count; $i++) {
                $func = self::$handler['befor'][$name][$i];
                $func($name, $arguments);
            }
        }
        $result = call_user_func_array('\\'.$name, $arguments);
        if (!empty(self::$handler['after'][$name])) {
            $count = count(self::$handler['after'][$name]);
            for ($i = 0; $i < $count; $i++) {
                $func = self::$handler['after'][$name][$i];
                $func($name, $arguments, $result);
            }
        }
        return $result;
    }
}