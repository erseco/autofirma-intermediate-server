# Contribuir

## Preparación

Necesitas PHP 7.4 o posterior, Composer 2 y `make`:

```bash
make install
make check
```

## Criterios

- Código, APIs, comentarios, docblocks y mensajes internos en inglés.
- README, guías y ADR en español.
- `declare(strict_types=1)` en cada fichero PHP.
- PSR-12, PHPStan al nivel máximo y pruebas para cada cambio observable.
- Sin dependencias de frameworks en el núcleo.
- Sin criptografía, validación de firmas ni persistencia documental.

Los cambios de protocolo, almacenamiento o límites de seguridad necesitan una
prueba de conformidad y un ADR nuevo. Los ADR aceptados son históricos: se
sustituyen mediante otro ADR, no se reescriben.

## Pull requests

Incluye una explicación del cambio, su impacto, las pruebas ejecutadas y
cualquier consideración de seguridad. No mezcles cambios ajenos al objetivo del
PR.
