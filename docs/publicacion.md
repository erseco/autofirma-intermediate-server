# Publicación y Packagist

## Metadatos

La descripción, las palabras clave y los identificadores de `composer.json`
están en inglés para facilitar la búsqueda internacional en Packagist. El README
y la documentación de uso permanecen en español; Packagist mostrará el README
del repositorio tal como está.

No declares `version` en `composer.json`. Composer y Packagist obtienen la
versión de los tags de Git.

## Alta inicial

1. Accede a [Packagist](https://packagist.org/).
2. Envía la URL `https://github.com/erseco/autofirma-intermediate-server`.
3. Activa la actualización automática mediante la integración de GitHub.
4. Comprueba que el nombre detectado sea
   `erseco/autofirma-intermediate-server`.

Este paso se realiza una sola vez y requiere la cuenta propietaria del paquete.
El workflow de GitHub no almacena credenciales de Packagist.

## Nueva versión

Antes del tag:

```bash
make install
make check
make dist
```

El tag debe tener forma `vX.Y.Z`. La persona responsable crea y publica el tag;
el agente no lo hace automáticamente:

```bash
git tag v0.1.0
git push origin v0.1.0
```

`release.yml` repite los controles, verifica el tag, genera un archivo con
`composer archive` y crea la GitHub Release. Packagist detecta el tag mediante
la integración configurada.

Una versión publicada no se modifica ni se reutiliza. Las correcciones se
publican con el siguiente número SemVer.
