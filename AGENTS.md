# Instrucciones para agentes

## Finalidad

Este repositorio implementa en PHP el protocolo de almacenamiento y
recuperación temporal que AutoScript utiliza para comunicarse con AutoFirma. No
firma, no valida firmas, no implementa criptografía y no es un repositorio de
documentos.

## Idioma y estilo

- Código, identificadores, APIs, comentarios, docblocks y mensajes internos en
  inglés.
- README, documentación, ADR, plantillas y explicaciones para personas en
  español.
- Los literales oficiales del protocolo se conservan aunque estén en español.
- PHP con cuatro espacios, `declare(strict_types=1)` y PSR-12.
- JSON, YAML, Markdown y NEON con dos espacios.
- Mantén compatibilidad con PHP 7.4; no uses sintaxis posterior.

## Arquitectura

- `src/IntermediateServer.php`: máquina de estados del protocolo.
- `src/Protocol/`: petición, respuesta y errores compatibles.
- `src/Storage/`: contrato y adaptadores de almacenamiento temporal.
- `src/Clock/`: reloj inyectable para TTL determinista.
- `src/Exception/`: errores de infraestructura.
- `tests/`: pruebas unitarias y de conformidad.
- `docs/arquitectura/adr/`: decisiones duraderas.

El núcleo debe seguir siendo independiente de WordPress, Symfony, Laravel,
PSR-7 y servidores concretos. Los adaptadores HTTP, tokens y políticas de CORS
pertenecen a los proyectos consumidores.

## Compatibilidad del protocolo

- La sintaxis admitida es `v=1_0`.
- `op=check` responde `OK`.
- `op=put` escribe o reemplaza un mensaje temporal.
- `op=get` consume el mensaje una sola vez.
- La ausencia de un identificador recuperable responde `ERR-06` para que
  AutoScript continúe el polling.
- Los errores funcionales del protocolo mantienen HTTP 200 y se expresan en el
  cuerpo.

Antes de cambiar estas reglas usa la skill
`.agents/skills/protocol-conformance/SKILL.md`, compara con las fuentes
oficiales fijadas en `docs/protocolo.md` y añade un ADR.

## Seguridad

- Trata `dat` como una cadena opaca; nunca la decodifiques ni la registres.
- No uses `id` directamente como nombre de fichero o clave sin normalización.
- Conserva TTL, límite de tamaño, consumo único y publicación atómica.
- No añadas CORS global ni autenticación basada en cookies al núcleo.
- No presentes una ruta sin token como despliegue seguro.
- Los cambios de almacenamiento o frontera HTTP requieren pruebas de carrera,
  expiración, traversal, sobrescritura y repetición.

Para una revisión usa `.agents/skills/security-review/SKILL.md`.

## Pruebas y controles

Antes de publicar cambios ejecuta:

```bash
make install
make check
```

`make check` es la misma puerta que CI: Composer, PSR-12, PHPStan, PHPUnit y
auditoría. Un cambio observable necesita tests en PHP 7.4 y en la versión
estable más reciente cubierta por la matriz.

## Releases

- No declares `version` en `composer.json`.
- Los tags tienen forma `vX.Y.Z` y siguen SemVer.
- Una versión publicada no se modifica ni se reutiliza.
- El tag lo crea una persona, nunca un agente.
- Packagist obtiene las versiones de los tags mediante su integración con
  GitHub.

Al preparar una publicación usa `.agents/skills/release/SKILL.md`.

## Documentación de arquitectura

Consulta `docs/arquitectura/adr/records.md` antes de tomar decisiones duraderas.

- Los IDs son correlativos y no se reutilizan.
- Los ADR aceptados son históricos: se sustituyen con otro ADR.
- Actualiza el registro al añadir una decisión.
