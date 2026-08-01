<?php

declare(strict_types=1);

namespace Erseco\AutoFirma\IntermediateServer\Tests\Unit;

use Erseco\AutoFirma\IntermediateServer\Exception\StorageException;
use Erseco\AutoFirma\IntermediateServer\IntermediateServer;
use Erseco\AutoFirma\IntermediateServer\Protocol\ProtocolError;
use Erseco\AutoFirma\IntermediateServer\Protocol\Request;
use Erseco\AutoFirma\IntermediateServer\Storage\StoreInterface;
use Erseco\AutoFirma\IntermediateServer\Tests\Support\InertStore;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Lo que responde el servidor cuando algo va mal.
 *
 * AutoScript solo entiende los códigos del protocolo, así que un fallo del
 * almacenamiento tiene que salir como `ERR-…` y no como una excepción: si la
 * petición muriera, quien está firmando se quedaría esperando sin explicación.
 */
final class ProtocolFailureTest extends TestCase
{
    private function serverWithBrokenStorage(): IntermediateServer
    {
        return new IntermediateServer(new class implements StoreInterface {
            public function put(string $identifier, string $payload, int $ttlSeconds): void
            {
                throw new StorageException('disco lleno');
            }

            public function consume(string $identifier): ?string
            {
                throw new StorageException('disco ilegible');
            }

            public function purgeExpired(): int
            {
                return 0;
            }
        });
    }

    public function testItRejectsANonPositiveMaximumPayload(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new IntermediateServer(new InertStore(), 0);
    }

    public function testItRejectsANonPositiveTtl(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new IntermediateServer(new InertStore(), 1024, 0);
    }

    public function testAStorageFailureWhileStoringBecomesAProtocolError(): void
    {
        $response = $this->serverWithBrokenStorage()->handle(new Request('POST', [
            'op' => 'put',
            'v' => '1_0',
            'id' => 'documento',
            'dat' => 'contenido',
        ]));

        self::assertSame(ProtocolError::storageFailure(), $response->body());
    }

    public function testAStorageFailureWhileRetrievingBecomesAProtocolError(): void
    {
        $response = $this->serverWithBrokenStorage()->handle(new Request('POST', [
            'op' => 'get',
            'v' => '1_0',
            'id' => 'documento',
        ]));

        self::assertStringStartsWith('ERR-', $response->body());
    }

    public function testRetrievingWithoutIdentifierIsRejected(): void
    {
        $response = $this->serverWithBrokenStorage()->handle(new Request('POST', [
            'op' => 'get',
            'v' => '1_0',
        ]));

        self::assertSame(ProtocolError::missingIdentifier(), $response->body());
    }

    public function testRetrievingWithAnInvalidIdentifierIsRejected(): void
    {
        $response = $this->serverWithBrokenStorage()->handle(new Request('POST', [
            'op' => 'get',
            'v' => '1_0',
            'id' => '../../etc/passwd',
        ]));

        self::assertSame(ProtocolError::invalidIdentifier(), $response->body());
    }

    public function testStoringWithAnInvalidIdentifierIsRejected(): void
    {
        $response = $this->serverWithBrokenStorage()->handle(new Request('POST', [
            'op' => 'put',
            'v' => '1_0',
            'id' => 'con espacios',
            'dat' => 'contenido',
        ]));

        self::assertSame(ProtocolError::invalidIdentifier(), $response->body());
    }

    /**
     * Los parámetros con clave numérica se descartan.
     *
     * `parse_str()` los produce cuando el cuerpo trae algo como `0=x`, y no
     * corresponden a ningún campo del protocolo.
     */
    public function testNumericParameterNamesAreIgnored(): void
    {
        $request = Request::fromRawHttp('POST', [], 'op=check&0=basura');

        self::assertSame('check', $request->value('op'));
        self::assertNull($request->value('0'));
    }
}
