# Protocolo intermedio de AutoScript

## Alcance

La implementación cubre la sintaxis `v=1_0` utilizada por los servicios
oficiales `StorageService` y `RetrieveService`. El servidor no interpreta el
documento, la firma ni la configuración cifrada.

## Operaciones

### Comprobación

```http
GET /storage/<token>?op=check
```

Respuesta:

```text
OK
```

AutoScript comprueba por separado las URL de almacenamiento y recuperación.

### Almacenamiento

```http
POST /storage/<token>
Content-Type: application/x-www-form-urlencoded

op=put&v=1_0&id=<identifier>&dat=<opaque-data>
```

Una escritura correcta responde `OK`. Una segunda escritura con el mismo
identificador reemplaza la anterior; AutoFirma puede usar este comportamiento
para publicar estados como `#WAIT` antes del resultado definitivo.

### Recuperación

```http
POST /retrieve/<token>
Content-Type: application/x-www-form-urlencoded

op=get&v=1_0&id=<identifier>&it=0
```

El parámetro `it` pertenece al polling de AutoScript y el servidor puede
ignorarlo. Si el identificador no existe o ha caducado, se responde:

```text
ERR-06:=El identificador para los datos es inválido
```

AutoScript interpreta `ERR-06` como «todavía no disponible» y vuelve a
intentarlo. Un resultado existente se entrega y elimina en la misma operación.

## Códigos conservados

| Código | Significado |
|---|---|
| `ERR-00` | Falta `op`. |
| `ERR-01` | Operación o versión no soportada. |
| `ERR-02` | Falta `dat`. |
| `ERR-05` | Falta `id`. |
| `ERR-06` | `id` inválido, inexistente o caducado. |
| `ERR-07` | Datos inválidos o demasiado grandes. |
| `ERR-18` | Fallo del almacenamiento. |
| `ERR-20` | Falta `v`. |

Los errores del protocolo usan HTTP 200 porque AutoScript decide el estado a
partir del cuerpo. Los errores del proxy, WAF o servidor web pueden usar códigos
HTTP, pero AutoScript los tratará como un fallo de comunicación.

## Fuentes de compatibilidad

- [`StorageService.java`](https://github.com/ctt-gob-es/clienteafirma/blob/v1.9.2/afirma-signature-storage/src/main/java/es/gob/afirma/signfolder/server/proxy/StorageService.java)
- [`RetrieveService.java`](https://github.com/ctt-gob-es/clienteafirma/blob/v1.9.2/afirma-signature-retriever/src/main/java/es/gob/afirma/signfolder/server/proxy/RetrieveService.java)
- [`autoscript.js`](https://github.com/ctt-gob-es/clienteafirma/blob/v1.9.2/afirma-ui-miniapplet-deploy/src/main/webapp/js/autoscript.js)

Una actualización de compatibilidad exige comparar esas tres fuentes, añadir
pruebas de conformidad y registrar la decisión mediante un ADR.
