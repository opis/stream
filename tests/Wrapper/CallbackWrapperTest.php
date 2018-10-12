<?php
/* ============================================================================
 * Copyright 2018 Zindex Software
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================================ */

namespace Opis\Stream\Test\Wrapper;

use Opis\Stream\Wrapper\CallbackStreamWrapper;
use PHPUnit\Framework\TestCase;

class CallbackWrapperTest extends TestCase
{

    public static function funcWithoutParams()
    {
        return strtoupper(__FUNCTION__);
    }

    public static function funcGetSum($args = null)
    {
        return $args ? array_sum($args) : 0;
    }

    public static function funcObjReturn($args = null)
    {
        return new class($args['data'] ?? null)
        {
            private $data;

            public function __construct($data)
            {
                $this->data = (string)$data;
            }

            /**
             * @inheritDoc
             */
            public function __toString()
            {
                return $this->data . ' altered';
            }
        };
    }

    public function testSimple()
    {
        $this->assertEquals(strtoupper('funcWithoutParams'), $this->getData('funcWithoutParams'));
    }

    public function testParams()
    {
        $this->assertEquals('6', $this->getData('funcGetSum', [
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ]));

        $this->assertEquals('my data altered', $this->getData('funcObjReturn', [
            'data' => 'my data',
        ]));
    }

    /**
     * @param string $method
     * @param array|null $params
     * @return bool|string
     */
    protected function getData(string $method, ?array $params = null)
    {
        $ctx = $params ? CallbackStreamWrapper::createContext($params) : null;

        return file_get_contents('callback://' . static::class . '::' . $method, false, $ctx);
    }

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        CallbackStreamWrapper::register();
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        CallbackStreamWrapper::unregister();
    }
}