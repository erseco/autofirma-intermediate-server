<?php

declare(strict_types=1);

namespace Erseco\AutoFirma\IntermediateServer\Tests\Unit;

use Erseco\AutoFirma\IntermediateServer\IntermediateServer;
use Erseco\AutoFirma\IntermediateServer\Protocol\Request;
use Erseco\AutoFirma\IntermediateServer\Storage\MemoryStore;
use Erseco\AutoFirma\IntermediateServer\Tests\Support\MutableClock;
use PHPUnit\Framework\TestCase;

final class IntermediateServerTest extends TestCase
{
    private IntermediateServer $server;

    protected function setUp(): void
    {
        parent::setUp();

        $this->server = new IntermediateServer(
            new MemoryStore(new MutableClock(1000)),
            16,
            60
        );
    }

    public function testHealthCheckReturnsOk(): void
    {
        $response = $this->server->handle(new Request('GET', ['op' => 'check']));

        self::assertSame(200, $response->statusCode());
        self::assertSame('OK', $response->body());
        self::assertSame('no-store', $response->headers()['Cache-Control']);
    }

    public function testItStoresAndConsumesProtocolPayloads(): void
    {
        $put = $this->server->handle(
            new Request(
                'POST',
                [
                    'op' => 'put',
                    'v' => '1_0',
                    'id' => 'Abc123_-',
                    'dat' => 'ciphertext',
                ]
            )
        );
        $get = $this->server->handle(
            new Request(
                'POST',
                [
                    'op' => 'get',
                    'v' => '1_0',
                    'id' => 'Abc123_-',
                    'it' => '0',
                ]
            )
        );
        $secondGet = $this->server->handle(
            new Request(
                'POST',
                [
                    'op' => 'get',
                    'v' => '1_0',
                    'id' => 'Abc123_-',
                ]
            )
        );

        self::assertSame('OK', $put->body());
        self::assertSame('ciphertext', $get->body());
        self::assertStringStartsWith('ERR-06:=', $secondGet->body());
    }

    /**
     * @dataProvider invalidRequestProvider
     *
     * @param array<string, mixed> $parameters
     */
    public function testItReturnsProtocolErrors(array $parameters, string $expectedPrefix): void
    {
        $response = $this->server->handle(new Request('POST', $parameters));

        self::assertStringStartsWith($expectedPrefix, $response->body());
        self::assertSame(200, $response->statusCode());
    }

    /**
     * @return iterable<string, array{0: array<string, mixed>, 1: string}>
     */
    public function invalidRequestProvider(): iterable
    {
        yield 'missing operation' => [[], 'ERR-00:='];
        yield 'unsupported operation' => [['op' => 'delete', 'v' => '1_0'], 'ERR-01:='];
        yield 'missing version' => [['op' => 'put'], 'ERR-20:='];
        yield 'unsupported version' => [['op' => 'put', 'v' => '2_0'], 'ERR-01:='];
        yield 'missing identifier' => [['op' => 'put', 'v' => '1_0'], 'ERR-05:='];
        yield 'invalid identifier' => [
            ['op' => 'put', 'v' => '1_0', 'id' => '../escape', 'dat' => 'data'],
            'ERR-06:=',
        ];
    }

    public function testMissingDataIsStoredAsAProtocolError(): void
    {
        $this->server->handle(
            new Request('POST', ['op' => 'put', 'v' => '1_0', 'id' => 'identifier'])
        );

        $response = $this->server->handle(
            new Request('POST', ['op' => 'get', 'v' => '1_0', 'id' => 'identifier'])
        );

        self::assertStringStartsWith('ERR-02:=', $response->body());
    }

    public function testOversizedDataIsStoredAsAProtocolError(): void
    {
        $this->server->handle(
            new Request(
                'POST',
                [
                    'op' => 'put',
                    'v' => '1_0',
                    'id' => 'identifier',
                    'dat' => str_repeat('x', 17),
                ]
            )
        );

        $response = $this->server->handle(
            new Request('POST', ['op' => 'get', 'v' => '1_0', 'id' => 'identifier'])
        );

        self::assertStringStartsWith('ERR-07:=', $response->body());
    }
}
