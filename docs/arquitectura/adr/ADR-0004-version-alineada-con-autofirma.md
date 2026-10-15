# ADR-0004: versión alineada con la línea de AutoFirma

- Estado: Aceptado
- Fecha: 2026-10-15

## Contexto

El servidor se usa junto a `@erseco/autofirma-client`, cuya versión sigue la
línea de AutoFirma (`1.9.x`), y tiene una versión gemela en TypeScript. Con
números independientes, quien integra los tres paquetes no sabe qué versiones
van juntas.

## Decisión

Mayor y menor acompañan a la línea de AutoFirma del cliente; el parche es
propio y solo crece. La primera versión publicada es `1.9.3`, y la gemela en
TypeScript publica los mismos números para el mismo comportamiento.

## Consecuencias

- `1.9.x` del servidor, del cliente y de la versión TypeScript van juntos.
- Un cambio incompatible del servidor espera a la siguiente línea o la abre,
  aunque AutoFirma no haya cambiado.
- El número no implica que el protocolo `v=1_0` cambie con AutoFirma.
