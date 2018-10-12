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

namespace Opis\Stream\Wrapper;

use Opis\Stream\{DataStream, IContent, IStream, IStreamWrapper};

abstract class AbstractContentStreamWrapper implements IStreamWrapper
{
    /** @var bool */
    protected static $registered = false;

    /** @var IContent[] */
    protected static $cached = [];

    /** @var resource|null */
    public $context;

    /** @var IStream */
    protected $stream = null;

    /**
     * @inheritDoc
     */
    public function stream_close(): void
    {
        if ($this->stream) {
            $this->stream->close();
        }
    }

    /**
     * @inheritDoc
     */
    public function stream_eof(): bool
    {
        return $this->stream ? $this->stream->isEOF() : true;
    }

    /**
     * @inheritDoc
     */
    public function stream_open(string $path, string $mode, int $options, ?string &$opened_path = null): bool
    {
        $this->stream = $this->stream($path, $mode);
        return $this->stream !== null;
    }

    /**
     * @inheritDoc
     */
    public function stream_read(int $count): ?string
    {
        return $this->stream ? $this->stream->read($count) : null;
    }

    /**
     * @inheritDoc
     */
    public function stream_stat(): ?array
    {
        return $this->stream ? $this->stream->stat() : null;
    }

    /**
     * @inheritDoc
     */
    public function stream_tell(): ?int
    {
        return $this->stream ? $this->stream->tell() : null;
    }

    /**
     * @inheritDoc
     */
    public function stream_seek(int $offset, int $whence = SEEK_SET): bool
    {
        return $this->stream ? $this->stream->seek($offset, $whence) : false;
    }

    /**
     * @inheritDoc
     */
    public function stream_flush(): bool
    {
        return $this->stream ? $this->stream->flush() : false;
    }

    /**
     * @inheritDoc
     */
    public function stream_lock(int $operation): bool
    {
        return $this->stream ? $this->stream->lock($operation) : false;
    }

    /**
     * @inheritDoc
     */
    public function stream_truncate(int $size): bool
    {
        return $this->stream ? $this->stream->truncate($size) : false;
    }

    /**
     * @inheritDoc
     */
    public function stream_write(string $data): ?int
    {
        return $this->stream ? $this->stream->write($data) : null;
    }

    /**
     * @inheritDoc
     */
    public function stream_cast(int $opt)
    {
        return $this->stream ? $this->stream->resource() : null;
    }

    /**
     * @param string $path
     * @param int $flags
     * @return array|null
     */
    public function url_stat(string $path, /** @noinspection PhpUnusedParameterInspection */int $flags): ?array
    {
        if (($stream = $this->stream($path, 'rb')) === null) {
            return null;
        }
        return $stream->stat();
    }

    /**
     * @param string $path
     * @param string $mode
     * @return null|IStream
     */
    protected function stream(string $path, string $mode): ?IStream
    {
        $key = md5($path);
        if (!array_key_exists($key, static::$cached)) {
            static::$cached[$key] = $this->content($path, $mode);
        }

        $content = static::$cached[$key];
        if ($content === null) {
            return null;
        }

        return $this->contentToStream($content);
    }

    /**
     * @param IContent $content
     * @return IStream
     */
    protected function contentToStream(IContent $content): IStream
    {
        return new DataStream($content->data(), $content->mode(), $content->created(), $content->updated());
    }

    /**
     * @param string $path
     * @param string $mode
     * @return null|IContent
     */
    abstract protected function content(string $path, string $mode): ?IContent;

    /**
     * @return bool
     */
    final public static function register(): bool
    {
        if (static::$registered) {
            return true;
        }
        if (!stream_wrapper_register(static::protocol(), static::class, static::protocolFlags())) {
            return false;
        }

        static::$registered = true;

        return true;
    }

    /**
     * @return bool
     */
    final public static function unregister(): bool
    {
        if (!static::$registered || !stream_wrapper_unregister(static::protocol())) {
            return false;
        }

        static::$registered = false;

        return true;
    }

    /**
     * @return bool
     */
    final public static function isRegistered(): bool
    {
        return static::$registered;
    }

    /**
     * @return int
     */
    protected static function protocolFlags(): int
    {
        return 0;
    }

    /**
     * @return string
     */
    abstract public static function protocol(): string;
}