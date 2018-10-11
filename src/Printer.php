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

use InvalidArgumentException;

class Printer
{
    /** @var IStream */
    protected $stream;

    /**
     * Printer constructor.
     * @param IStream $stream
     */
    public function __construct(IStream $stream)
    {
        if ($stream->isClosed() || !$stream->isWritable()) {
            throw new InvalidArgumentException('Stream is not writable');
        }

        $this->stream = $stream;
    }

    /**
     * @return IStream
     */
    public function stream(): IStream
    {
        return $this->stream;
    }

    /**
     * Writes the formatted string to stream
     * @param string $format
     * @param mixed ...$args
     * @return bool
     * @see sprintf()
     */
    public function print(string $format, ...$args): bool
    {
        return $this->doWrite(sprintf($format, ...$args)) !== null;
    }

    /**
     * Writes the formatted string to stream and restores previous pointer position
     * @param string $format
     * @param mixed ...$args
     * @return bool
     * @see sprintf()
     */
    public function printRestore(string $format, ...$args): bool
    {
        return $this->doWrite(sprintf($format, ...$args), true) !== null;
    }

    /**
     * Writes data to stream
     * @param string $data
     * @param bool $restore True to restore previous pointer position
     * @return int|null
     */
    public function write(string $data, bool $restore = false): ?int
    {
        return $this->doWrite($data, $restore);
    }

    /**
     * @param string $data
     * @param bool $restore
     * @return int|null
     */
    protected function doWrite(string $data, bool $restore = false): ?int
    {
        $stream = $this->stream;
        if (!$restore) {
            return $stream->write($data);
        }

        if (!$stream->isSeekable()) {
            return null;
        }

        if (($pos = $stream->tell()) === null) {
            return null;
        }

        if (($len = $stream->write($data)) === null) {
            return null;
        }

        $stream->seek($pos);

        return $len;
    }
}