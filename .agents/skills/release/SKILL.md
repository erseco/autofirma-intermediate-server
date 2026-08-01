---
name: release
description: Preparar y verificar una publicación de erseco/autofirma-intermediate-server en GitHub y Packagist. Úsala al proponer versión, comprobar un tag, generar el archivo Composer o seguir el workflow de release.
---

# Preparar una publicación

Una versión visible en Packagist no se modifica ni se reutiliza. El tag lo crea
la persona responsable, nunca el agente.

## Puertas previas

```bash
make install
make check
rm -rf artifacts
make dist
```

Comprobar que el ZIP contiene `composer.json`, `src/`, `README.md` y `LICENSE`, y
que excluye tests, workflows, skills y dependencias de desarrollo.

## Versión

Elegir `X.Y.Z` mediante SemVer y actualizar `CHANGELOG.md`. No añadir el campo
`version` a `composer.json`. El tag correspondiente es `vX.Y.Z`.

Dejar estas órdenes a la persona responsable:

```bash
git tag vX.Y.Z
git push origin vX.Y.Z
```

## Verificación

1. Seguir `release.yml` hasta que termine.
2. Comprobar que existe la GitHub Release y su archivo ZIP.
3. Verificar en Packagist que la versión y requisitos de PHP coinciden.
4. Instalar la versión desde un proyecto vacío con Composer.

Si Packagist no actualiza, revisar la integración de GitHub antes de reenviar el
repositorio manualmente. No guardar tokens de Packagist en el código.
