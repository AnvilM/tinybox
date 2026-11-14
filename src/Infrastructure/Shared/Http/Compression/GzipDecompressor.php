<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Http\Compression;

use InflateContext;
use Psl\Compression\DecompressorInterface;
use Psl\Compression\Exception\RuntimeException;
use function inflate_add;
use function inflate_get_status;
use function inflate_init;
use function strlen;
use const ZLIB_ENCODING_GZIP;
use const ZLIB_STREAM_END;

/**
 * Incremental gzip decompressor based on ext-zlib.
 *
 * The size of the decompressed data is limited to protect against decompression bombs.
 */
final class GzipDecompressor implements DecompressorInterface
{
    private ?InflateContext $context = null;
    private bool $streamEnded = false;
    private int $producedBytes = 0;

    /**
     * @param positive-int $maxOutputSize Maximum size of the decompressed data in bytes
     */
    public function __construct(
        private readonly int $maxOutputSize,
    )
    {
    }

    public function push(string $data): string
    {
        if ($this->context === null) {
            $context = @inflate_init(ZLIB_ENCODING_GZIP);

            if ($context === false) {
                throw new RuntimeException('Unable to initialize gzip decompression');
            }

            $this->context = $context;
        }

        $output = @inflate_add($this->context, $data);

        if ($output === false) {
            $this->reset();

            throw new RuntimeException('Unable to decompress gzip data');
        }

        $this->producedBytes += strlen($output);

        if ($this->producedBytes > $this->maxOutputSize) {
            $this->reset();

            throw new RuntimeException("Decompressed data exceeds {$this->maxOutputSize} bytes");
        }

        $this->streamEnded = inflate_get_status($this->context) === ZLIB_STREAM_END;

        return $output;
    }

    public function finish(): string
    {
        $isComplete = $this->context === null || $this->streamEnded;

        $this->reset();

        if (!$isComplete) {
            throw new RuntimeException('Gzip data is truncated');
        }

        return '';
    }

    private function reset(): void
    {
        $this->context = null;
        $this->streamEnded = false;
        $this->producedBytes = 0;
    }
}
