<?php

declare(strict_types=1);

namespace Erseco\AutoFirma\IntermediateServer\Tests\Unit;

use Erseco\AutoFirma\IntermediateServer\Protocol\Request;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    public function testItParsesQueryAndFormParameters(): void
    {
        $request = Request::fromRawHttp(
            'post',
            ['op' => 'put'],
            'v=1_0&id=abc123&dat=payload'
        );

        self::assertSame('POST', $request->method());
        self::assertSame('put', $request->value('op'));
        self::assertSame('1_0', $request->value('v'));
        self::assertSame('abc123', $request->value('id'));
        self::assertSame('payload', $request->value('dat'));
    }

    public function testFormParametersOverrideQueryParameters(): void
    {
        $request = Request::fromRawHttp('POST', ['id' => 'query'], 'id=form');

        self::assertSame('form', $request->value('id'));
    }

    public function testItRejectsArrayParameters(): void
    {
        $request = new Request('POST', ['id' => ['unexpected']]);

        self::assertNull($request->value('id'));
    }
}
