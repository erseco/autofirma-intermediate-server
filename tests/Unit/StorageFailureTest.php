<?php

declare(strict_types=1);

namespace Erseco\AutoFirma\IntermediateServer\Tests\Unit;

use Erseco\AutoFirma\IntermediateServer\Clock\SystemClock;
use Erseco\AutoFirma\IntermediateServer\Exception\StorageException;
use Erseco\AutoFirma\IntermediateServer\Storage\FilesystemStore;
use Erseco\AutoFirma\IntermediateServer\Storage\MemoryStore;
use Erseco\AutoFirma\IntermediateServer\Tests\Support\MutableClock;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Los caminos por los que el almacenamiento se niega a trabajar.
 *
 * Un servidor intermedio guarda datos ajenos durante unos segundos, así que sus
 * defensas importan tanto como su camino feliz: un directorio que resulta ser un
 * enlace, un fichero que aparece donde debía haber un mensaje, un registro con
 * la caducidad manipulada.
 */
final class StorageFailureTest extends TestCase
{
    /** @var list<string> */
    private array $paths = [];

    protected function tearDown(): void
    {
        foreach (array_reverse($this->paths) as $path) {
            if (is_link($path) || is_file($path)) {
                @unlink($path);
            } elseif (is_dir($path)) {
                @chmod($path, 0700);

                $children = glob($path . '/*');

                foreach (is_array($children) ? $children : [] as $child) {
                    if (is_link($child) || is_file($child)) {
                        @unlink($child);
                    } elseif (is_dir($child)) {
                        @rmdir($child);
                    }
                }

                @rmdir($path);
            }
        }

        $this->paths = [];
    }

    private function directory(): string
    {
        $path = sys_get_temp_dir() . '/afis-' . bin2hex(random_bytes(6));
        $this->paths[] = $path;

        return $path;
    }

    public function testItRefusesADirectoryThatIsASymbolicLink(): void
    {
        $target = $this->directory();
        mkdir($target, 0700, true);

        $link = $this->directory();
        symlink($target, $link);

        $this->expectException(StorageException::class);

        new FilesystemStore($link, new SystemClock());
    }

    public function testItRefusesADirectoryItCannotCreate(): void
    {
        $file = $this->directory();
        file_put_contents($file, 'no soy un directorio');

        // `mkdir()` avisa además de devolver false, y PHPUnit convierte ese
        // aviso en un error antes de que la excepción llegue a lanzarse. Lo que
        // se comprueba aquí es la excepción, no el aviso.
        set_error_handler(static fn (): bool => true);

        try {
            $this->expectException(StorageException::class);

            new FilesystemStore($file . '/dentro', new SystemClock());
        } finally {
            restore_error_handler();
        }
    }

    public function testItRefusesADirectoryItCannotWrite(): void
    {
        if (0 === posix_geteuid()) {
            self::markTestSkipped('Como root los permisos del directorio no se respetan.');
        }

        $directory = $this->directory();
        mkdir($directory, 0500, true);

        $this->expectException(StorageException::class);

        new FilesystemStore($directory, new SystemClock());
    }

    public function testItRejectsANonPositiveTtl(): void
    {
        $store = new FilesystemStore($this->directory(), new SystemClock());

        $this->expectException(InvalidArgumentException::class);

        $store->put('identifier', 'payload', 0);
    }

    public function testMemoryStoreRejectsANonPositiveTtl(): void
    {
        $store = new MemoryStore(new MutableClock(1000));

        $this->expectException(InvalidArgumentException::class);

        $store->put('identifier', 'payload', 0);
    }

    /**
     * Un enlace donde debería haber un mensaje no se sigue: se borra.
     *
     * Seguirlo permitiría que quien pueda escribir en el directorio hiciera que
     * el servidor entregase cualquier fichero al que llegue el proceso.
     */
    public function testItRefusesToFollowASymbolicLinkWhenConsuming(): void
    {
        $directory = $this->directory();
        $store = new FilesystemStore($directory, new SystemClock());

        $secret = $directory . '/secreto';
        file_put_contents($secret, 'contenido ajeno');

        $message = $directory . '/' . hash('sha256', 'identifier') . '.message';
        symlink($secret, $message);

        self::assertNull($store->consume('identifier'));
        self::assertFileExists($secret, 'El fichero apuntado no debe tocarse.');
    }

    public function testPurgeRemovesSymbolicLinks(): void
    {
        $directory = $this->directory();
        $store = new FilesystemStore($directory, new SystemClock());

        $target = $directory . '/objetivo';
        file_put_contents($target, 'contenido');

        $link = $directory . '/' . hash('sha256', 'enlazado') . '.message';
        symlink($target, $link);

        self::assertSame(1, $store->purgeExpired());
        self::assertFalse(is_link($link));
        self::assertFileExists($target);
    }

    public function testPurgeIgnoresDirectoriesThatLookLikeMessages(): void
    {
        $directory = $this->directory();
        $store = new FilesystemStore($directory, new SystemClock());

        $intruder = $directory . '/' . hash('sha256', 'directorio') . '.message';
        mkdir($intruder, 0700, true);

        self::assertSame(0, $store->purgeExpired());
        self::assertDirectoryExists($intruder);
    }

    /**
     * Una caducidad que no es un número se trata como registro corrupto.
     */
    public function testARecordWithANonNumericExpirationIsDiscarded(): void
    {
        $directory = $this->directory();
        $store = new FilesystemStore($directory, new MutableClock(1000));

        $message = $directory . '/' . hash('sha256', 'manipulado') . '.message';
        file_put_contents($message, str_repeat('x', 20) . "\n" . 'contenido');

        self::assertNull($store->consume('manipulado'));
    }

    /**
     * Y uno demasiado corto para llevar caducidad, también.
     */
    public function testATruncatedRecordIsDiscarded(): void
    {
        $directory = $this->directory();
        $store = new FilesystemStore($directory, new MutableClock(1000));

        $message = $directory . '/' . hash('sha256', 'truncado') . '.message';
        file_put_contents($message, 'corto');

        self::assertNull($store->consume('truncado'));
    }

    public function testSystemClockReturnsTheCurrentTime(): void
    {
        $before = time();
        $now = (new SystemClock())->now();

        self::assertGreaterThanOrEqual($before, $now);
        self::assertLessThanOrEqual(time(), $now);
    }
}
