<?php

declare(strict_types=1);

namespace Erseco\AutoFirma\IntermediateServer\Tests\Unit;

use Erseco\AutoFirma\IntermediateServer\Storage\MemoryStore;
use Erseco\AutoFirma\IntermediateServer\Tests\Support\MutableClock;
use PHPUnit\Framework\TestCase;

final class MemoryStoreTest extends TestCase
{
    public function testItConsumesAStoredPayloadOnlyOnce(): void
    {
        $store = new MemoryStore(new MutableClock(1000));
        $store->put('identifier', 'payload', 60);

        self::assertSame('payload', $store->consume('identifier'));
        self::assertNull($store->consume('identifier'));
    }

    public function testExpiredPayloadsCannotBeConsumed(): void
    {
        $clock = new MutableClock(1000);
        $store = new MemoryStore($clock);
        $store->put('identifier', 'payload', 60);
        $clock->advance(60);

        self::assertNull($store->consume('identifier'));
    }

    public function testItPurgesExpiredPayloads(): void
    {
        $clock = new MutableClock(1000);
        $store = new MemoryStore($clock);
        $store->put('expired', 'old', 10);
        $store->put('active', 'new', 20);
        $clock->advance(10);

        self::assertSame(1, $store->purgeExpired());
        self::assertSame('new', $store->consume('active'));
    }

    public function testPuttingTheSameIdentifierReplacesItsPayload(): void
    {
        $store = new MemoryStore(new MutableClock(1000));
        $store->put('identifier', '#WAIT', 60);
        $store->put('identifier', 'result', 60);

        self::assertSame('result', $store->consume('identifier'));
    }
}
