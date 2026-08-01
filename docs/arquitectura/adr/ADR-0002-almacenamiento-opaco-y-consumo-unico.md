# ADR-0002: almacenamiento opaco y consumo único

- Estado: Aceptado
- Fecha: 2026-08-01

## Contexto

AutoScript y AutoFirma cifran los mensajes y coordinan el intercambio mediante
identificadores temporales. El servidor no necesita conocer el contenido y una
segunda lectura permitiría reproducir resultados.

## Decisión

`StoreInterface` almacena cadenas opacas con TTL. `consume()` recupera y elimina
atómicamente. Una escritura posterior con el mismo identificador reemplaza la
anterior para conservar el comportamiento de estados intermedios como `#WAIT`.

## Consecuencias

- El servidor no interpreta documentos ni firmas.
- Los adaptadores distribuidos deben implementar una operación atómica.
- Los mensajes abandonados requieren una limpieza periódica.
