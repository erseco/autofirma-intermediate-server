---
name: protocol-conformance
description: Verificar o modificar la compatibilidad del servidor PHP con el protocolo intermedio de AutoScript y AutoFirma. Úsala al cambiar operaciones, parámetros, errores, polling, TTL observable, almacenamiento o la versión oficial compatible.
---

# Comprobar la conformidad del protocolo

## Fuentes

Leer primero `docs/protocolo.md` y `docs/arquitectura/adr/records.md`. Comparar
el comportamiento con las tres fuentes oficiales enlazadas en la documentación:
`autoscript.js`, `StorageService.java` y `RetrieveService.java`.

No deducir el protocolo únicamente de una de ellas: el navegador y la
aplicación nativa usan sentidos opuestos del mismo almacén.

## Flujo

1. Identificar el tag oficial afectado y la versión interna de AutoScript.
2. Registrar para `check`, `put` y `get` método, parámetros, respuesta y efecto
   sobre el almacén.
3. Comprobar los casos ausente, inválido, caducado, sobrescrito y consumido.
4. Mantener HTTP 200 para errores que AutoScript interpreta por el cuerpo.
5. Añadir o actualizar pruebas antes de cambiar la implementación.
6. Ejecutar `make check` en PHP 7.4 y en la versión superior de CI.
7. Añadir un ADR si cambia una garantía pública o la versión compatible.

## Invariantes

- No interpretar ni descifrar `dat`.
- No entregar dos veces un resultado.
- Permitir reemplazar un identificador para estados como `#WAIT`.
- Responder `ERR-06` cuando el resultado aún no existe.
- No cambiar literales o separadores sin una prueba contra AutoScript real.

Terminar indicando la fuente oficial revisada, las pruebas añadidas y cualquier
divergencia intencional.
