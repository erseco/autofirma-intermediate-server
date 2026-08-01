# ADR-0003: seguridad adicional en los adaptadores

- Estado: Aceptado
- Fecha: 2026-08-01

## Contexto

El protocolo oficial usa endpoints públicos sin un mecanismo de autenticación
propio. Añadir parámetros obligatorios a `op=put` o `op=get` rompería la
compatibilidad con AutoScript y AutoFirma.

## Decisión

El núcleo conserva el protocolo. Los adaptadores deben añadir tokens efímeros
en el path, rate limiting, CORS explícito, HTTPS y límites de infraestructura
antes de delegar en `IntermediateServer`.

## Consecuencias

- Es posible mantener compatibilidad exacta.
- Una instancia del núcleo no constituye por sí sola un endpoint seguro.
- Toda documentación de un adaptador debe describir su mecanismo de token.
