<?php

declare(strict_types=1);

namespace Erseco\AutoFirma\IntermediateServer\Tests\Unit;

use Erseco\AutoFirma\IntermediateServer\Storage\FilesystemStore;
use Erseco\AutoFirma\IntermediateServer\Tests\Support\MutableClock;
use PHPUnit\Framework\TestCase;

final class FilesystemStoreTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->directory = sys_get_temp_dir()
            . DIRECTORY_SEPARATOR
            . 'autofirma-intermediate-' . bin2hex(random_bytes(8));
    }

    protected function tearDown(): void
    {
        $paths = glob($this->directory . DIRECTORY_SEPARATOR . '*');

        if (is_array($paths)) {
            foreach ($paths as $path) {
                if (is_file($path)) {
                    unlink($path);
                }
            }
        }

        if (is_dir($this->directory)) {
            rmdir($this->directory);
        }

        parent::tearDown();
    }

    public function testItCreatesThePrivateDirectoryAndConsumesOnce(): void
    {
        $store = new FilesystemStore($this->directory, new MutableClock(1000));
        $store->put('identifier', 'payload', 60);

        self::assertDirectoryExists($this->directory);
        self::assertSame('payload', $store->consume('identifier'));
        self::assertNull($store->consume('identifier'));
    }

    public function testItHashesIdentifiersBeforeUsingThemAsFilenames(): void
    {
        $store = new FilesystemStore($this->directory, new MutableClock(1000));
        $store->put('../identifier', 'payload', 60);

        self::assertFileDoesNotExist(dirname($this->directory) . '/identifier');
        self::assertSame('payload', $store->consume('../identifier'));
    }

    public function testExpiredPayloadsAreRemovedOnConsumption(): void
    {
        $clock = new MutableClock(1000);
        $store = new FilesystemStore($this->directory, $clock);
        $store->put('identifier', 'payload', 10);
        $clock->advance(10);

        self::assertNull($store->consume('identifier'));
        self::assertSame([], glob($this->directory . DIRECTORY_SEPARATOR . '*'));
    }

    public function testItPurgesExpiredAndMalformedRecords(): void
    {
        $clock = new MutableClock(1000);
        $store = new FilesystemStore($this->directory, $clock);
        $store->put('expired', 'old', 10);
        $store->put('active', 'new', 20);
        file_put_contents($this->directory . '/malformed.message', 'invalid');
        $clock->advance(10);

        self::assertSame(2, $store->purgeExpired());
        self::assertSame('new', $store->consume('active'));
    }
}
