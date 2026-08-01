# Uso e integración

## Responsabilidades

La librería recibe parámetros ya transportados por HTTP, aplica el protocolo de
AutoScript y delega la persistencia temporal en `StoreInterface`. El adaptador
de cada framework debe:

1. resolver y comprobar el token efímero de la ruta;
2. construir `Request` con query y cuerpo;
3. ejecutar `IntermediateServer::handle()`;
4. emitir código, cabeceras y cuerpo de `Response` sin transformarlos;
5. aplicar límites de red y rate limiting.

## Construcción desde una petición HTTP

```php
$request = Request::fromRawHttp($method, $query, $rawBody);
$response = $server->handle($request);
```

`fromRawHttp()` acepta parámetros en la query y en cuerpos
`application/x-www-form-urlencoded`. Si una clave aparece en ambos sitios, el
cuerpo tiene prioridad.

## Configuración

```php
$server = new IntermediateServer(
    $store,
    20 * 1024 * 1024,
    120
);
```

El segundo argumento es el máximo del campo `dat` ya codificado. No equivale al
tamaño del PDF original: Base64 y el cifrado aumentan el cuerpo. El tercero es
el TTL de cada mensaje en segundos.

## Sistema de ficheros

```php
$store = new FilesystemStore(
    '/var/lib/autofirma-intermediate',
    new SystemClock()
);
```

El directorio se crea con permisos `0700`, los nombres recibidos nunca se usan
como nombres de fichero y cada mensaje se publica mediante un renombrado
atómico. El directorio debe estar fuera del document root.

En una aplicación con varios contenedores, todos deben compartir ese volumen.
Si no es posible, implementa `StoreInterface` sobre Redis y realiza `consume()`
mediante una operación atómica de lectura y eliminación.

## Limpieza

Los mensajes normales desaparecen al consumirse. Los abandonados se eliminan
con:

```php
$removed = $store->purgeExpired();
```

Ejecuta esta operación periódicamente desde cron, una tarea programada del
framework o un proceso de mantenimiento. No la ejecutes en cada petición si el
almacén puede contener muchos elementos.

## Integración con WordPress

El futuro adaptador de WordPress debe registrar dos rutas públicas que compartan
almacén, pero no debe usar `permission_callback` como único control: AutoFirma
no puede enviar la cookie ni el nonce REST de WordPress. La autorización debe
basarse en un token efímero incluido en la ruta y verificado antes de llamar a
la librería.

No uses transients para documentos grandes: dependiendo de la instalación
pueden acabar en `wp_options`. Para un único servidor usa un directorio privado;
para varios nodos usa Redis u otro almacén compartido.
