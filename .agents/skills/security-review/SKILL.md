---
name: security-review
description: Revisar la seguridad del servidor intermedio de AutoFirma y sus adaptadores. Úsala al auditar almacenamiento, rutas públicas, tokens, límites, CORS, logging, concurrencia, expiración, despliegues multinodo o denegación de servicio.
---

# Revisar la seguridad

## Preparación

Leer `docs/seguridad.md`, los ADR y el código del adaptador que expone HTTP. No
considerar segura una instancia del núcleo si el adaptador no añade token
efímero y controles operativos.

## Recorrido mínimo

1. Seguir `op=put` desde el cuerpo HTTP hasta el almacén.
2. Seguir `op=get` hasta la eliminación y demostrar que el consumo es atómico.
3. Intentar traversal, enlaces simbólicos, identificadores largos, parámetros
   de tipo array y registros malformados.
4. Probar sobrescritura, doble lectura, carreras y expiración en el límite.
5. Verificar tamaños en proxy, PHP y librería, incluida la expansión Base64.
6. Revisar token, TTL, replay, rate limiting, CORS, caché y logging del adaptador.
7. Comprobar afinidad o almacenamiento compartido en despliegues multinodo.
8. Ejecutar `make check` y pruebas dinámicas para cada hallazgo reproducible.

## Informe

Separar vulnerabilidades explotables de recomendaciones de endurecimiento. Para
cada vulnerabilidad indicar atacante, petición concreta, impacto, evidencia y
corrección. No elevar la ausencia de una segunda defensa si otra capa bloquea
completamente el ataque.
