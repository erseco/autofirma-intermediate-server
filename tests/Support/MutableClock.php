<?php

declare(strict_types=1);

namespace Erseco\AutoFirma\IntermediateServer\Tests\Support;

use Erseco\AutoFirma\IntermediateServer\Clock\ClockInterface;

final class MutableClock implements ClockInterface
{
    private int $timestamp;

    public function __construct(int $timestamp)
    {
        $this->timestamp = $timestamp;
    }

    public function advance(int $seconds): void
    {
        $this->timestamp += $seconds;
    }

    public function now(): int
    {
        return $this->timestamp;
    }
}
