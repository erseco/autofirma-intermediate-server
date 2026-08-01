# ADR-0001: núcleo independiente de frameworks

- Estado: Aceptado
- Fecha: 2026-08-01

## Contexto

El mismo protocolo debe poder integrarse en WordPress, Symfony, Laravel o PHP
puro. Depender de la petición o respuesta de un framework obligaría a duplicar
la lógica o arrastrar dependencias innecesarias.

## Decisión

El paquete expone objetos propios `Request` y `Response`, un
`IntermediateServer` y un contrato `StoreInterface`. No depende de PSR-7 ni de
ningún framework. Cada integración adapta su frontera HTTP.

## Consecuencias

- El núcleo se prueba sin servidor web.
- Los adaptadores son pequeños y reemplazables.
- Cada framework debe emitir las cabeceras y aplicar autorización por su cuenta.
