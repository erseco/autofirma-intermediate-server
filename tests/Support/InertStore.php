<?php

declare(strict_types=1);

namespace Erseco\AutoFirma\IntermediateServer\Tests\Support;

use Erseco\AutoFirma\IntermediateServer\Storage\StoreInterface;

/**
 * Almacén que no guarda nada, para comprobaciones que no llegan a usarlo.
 */
final class InertStore implements StoreInterface
{
    public function put(string $identifier, string $payload, int $ttlSeconds): void
    {
    }

    public function consume(string $identifier): ?string
    {
        return null;
    }

    public function purgeExpired(): int
    {
        return 0;
    }
}
