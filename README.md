# AutoFirma Intermediate Server

[![CI](https://github.com/erseco/autofirma-intermediate-server/actions/workflows/ci.yml/badge.svg)](https://github.com/erseco/autofirma-intermediate-server/actions/workflows/ci.yml)
[![Codecov](https://codecov.io/gh/erseco/autofirma-intermediate-server/graph/badge.svg)](https://codecov.io/gh/erseco/autofirma-intermediate-server)
[![Packagist](https://img.shields.io/packagist/v/erseco/autofirma-intermediate-server.svg)](https://packagist.org/packages/erseco/autofirma-intermediate-server)
[![PHP](https://img.shields.io/packagist/php-v/erseco/autofirma-intermediate-server.svg)](https://packagist.org/packages/erseco/autofirma-intermediate-server)
[![License](https://img.shields.io/github/license/erseco/autofirma-intermediate-server)](LICENSE)

Implementación PHP, independiente de frameworks, del servidor intermedio que
AutoScript utiliza para intercambiar datos entre una aplicación web y
AutoFirma.

> [!IMPORTANT]
> Este proyecto no pertenece al Gobierno de España, no incluye AutoFirma y no
> realiza ni valida firmas electrónicas. Solo transporta temporalmente datos
> opacos que AutoScript y AutoFirma cifran y descifran.

## Para qué sirve

En dispositivos móviles y en entornos donde la comunicación local mediante
WebSocket no está disponible, AutoScript necesita dos servicios HTTP:

- **storage**: recibe temporalmente la petición o el resultado cifrado;
- **retrieve**: entrega el dato una sola vez y lo elimina.

Ambos servicios deben usar el mismo almacenamiento. La librería implementa el
protocolo `v=1_0`, sus respuestas de compatibilidad y el consumo único, pero no
impone WordPress, Symfony, Laravel ni una implementación HTTP concreta.

## Instalación

```bash
composer require erseco/autofirma-intermediate-server
```

## Uso básico

```php
<?php

declare(strict_types=1);

use Erseco\AutoFirma\IntermediateServer\Clock\SystemClock;
use Erseco\AutoFirma\IntermediateServer\IntermediateServer;
use Erseco\AutoFirma\IntermediateServer\Protocol\Request;
use Erseco\AutoFirma\IntermediateServer\Storage\FilesystemStore;

require __DIR__ . '/vendor/autoload.php';

$clock = new SystemClock();
$store = new FilesystemStore('/var/lib/autofirma-intermediate', $clock);
$server = new IntermediateServer($store);
$body = file_get_contents('php://input');
$request = Request::fromRawHttp(
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    $_GET,
    $body === false ? '' : $body
);
$response = $server->handle($request);

http_response_code($response->statusCode());

foreach ($response->headers() as $name => $value) {
    header($name . ': ' . $value);
}

echo $response->body();
```

Después se pasan las dos URL públicas a
[`@erseco/autofirma-client`](https://github.com/erseco/autofirma-client):

```js
const client = new AutoFirmaClient({
  storageUrl: "https://example.org/autofirma/storage/<token>",
  retrieveUrl: "https://example.org/autofirma/retrieve/<token>",
});
```

Las dos rutas pueden delegar en la misma instancia de `IntermediateServer`.
La separación de URL existe por compatibilidad con AutoScript.

## Almacenamientos incluidos

- `FilesystemStore`: producción en un único nodo o con un volumen compartido.
- `MemoryStore`: pruebas y procesos persistentes; no sirve para PHP-FPM porque
  su contenido desaparece al terminar cada petición.

Para Redis, una base de datos u otro sistema distribuido, implementa
`StoreInterface`. `consume()` debe ser atómico: ningún resultado puede
entregarse dos veces.

## Seguridad

Las rutas son utilizadas tanto por el navegador como por AutoFirma y no pueden
depender de una cookie de sesión. No publiques una URL fija y anónima sin una
capa adicional de protección.

Como mínimo:

- usa exclusivamente HTTPS;
- incluye un token opaco y efímero en la ruta;
- limita tamaño, tasa de peticiones y tiempo de vida;
- almacena fuera de cualquier directorio público;
- desactiva la caché HTTP y evita registrar cuerpos o identificadores;
- configura un almacenamiento compartido si hay varios nodos.

Consulta [`docs/seguridad.md`](docs/seguridad.md) antes de desplegarla.

## Desarrollo

```bash
make install
make check
```

Los controles incluyen validación de Composer, PSR-12, PHPStan al nivel máximo,
PHPUnit y auditoría de dependencias.

## Documentación

- [Uso e integración](docs/uso.md)
- [Protocolo](docs/protocolo.md)
- [Seguridad](docs/seguridad.md)
- [Publicación y Packagist](docs/publicacion.md)
- [Decisiones de arquitectura](docs/arquitectura/adr/records.md)

## Licencia

GPL-2.0-or-later. AutoFirma y AutoScript mantienen sus propias licencias y
marcas.
