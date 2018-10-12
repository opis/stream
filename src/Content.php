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

namespace Opis\Stream;

class Content implements IContent
{
    /** @var mixed */
    protected $data;
    /** @var int */
    protected $created;
    /** @var int */
    protected $updated;
    /** @var string */
    protected $mode;
    /** @var string */
    protected $type;
    /** @var string|null */
    protected $string = null;

    /**
     * Content constructor.
     * @param string|callable $data
     * @param null|string $mode
     * @param int|null $created
     * @param int|null $updated
     * @param null|string $type
     */
    public function __construct($data, ?string $mode = null, ?int $created = null, ?int $updated = null, ?string $type = null)
    {
        $this->data = $data;
        $this->mode = $mode ?? 'rb';
        $this->created = $created ?? time();
        $this->updated = $updated ?? $this->created;
        $this->type = $type ?? 'text/plain';
    }

    /**
     * @inheritDoc
     */
    public function data(): string
    {
        if ($this->string === null) {
            $data = $this->data;
            $this->data = null;
            $this->string = (string)(is_callable($data) ? $data() : $data);
        }
        return $this->string;
    }

    /**
     * @inheritDoc
     */
    public function created(): int
    {
        return $this->created;
    }

    /**
     * @inheritDoc
     */
    public function updated(): int
    {
        return $this->updated;
    }

    /**
     * @inheritDoc
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function mode(): string
    {
        return $this->mode;
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->data();
    }
}