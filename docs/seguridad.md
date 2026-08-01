# Seguridad

## Modelo de amenaza

Los endpoints deben ser accesibles desde un navegador y desde AutoFirma. Esto
impide basar su acceso únicamente en cookies, nonces del navegador o sesiones
del framework. Un atacante puede alcanzar las rutas y tratar de:

- agotar memoria, disco o conexiones mediante escrituras grandes;
- adivinar identificadores y consumir resultados ajenos;
- sobrescribir un identificador antes que AutoFirma;
- forzar reintentos continuos;
- recuperar datos desde cachés o logs;
- aprovechar diferencias entre varios nodos.

El cifrado de AutoScript protege el contenido frente al servidor intermedio,
pero no sustituye los controles operativos.

## Controles obligatorios del adaptador

### Token efímero

Incluye en ambas rutas un token aleatorio o firmado, asociado a una operación y
con caducidad corta. El token debe validarse antes de procesar `op`. No lo pongas
en una query si el proxy registra URLs completas; AutoScript permite incluirlo
en el path.

### Límites

- Configura `maxPayloadBytes` de forma explícita.
- Ajusta también `client_max_body_size`, `post_max_size` y cualquier límite del
  proxy o CDN.
- Aplica rate limiting por token y por dirección IP.
- Limita la cantidad de identificadores activos por token.

### Almacenamiento

- Usa un directorio privado con permisos mínimos o un almacén con TTL nativo.
- No uses la biblioteca de medios ni un bucket público.
- Garantiza lectura y eliminación atómicas.
- Comparte el almacén entre nodos o aplica afinidad de sesión.
- Ejecuta `purgeExpired()` periódicamente.

### HTTP

- HTTPS es obligatorio.
- Devuelve `Cache-Control: no-store`.
- No habilites `Access-Control-Allow-Origin: *` por defecto. Si la aplicación
  web está en otro origen, usa una lista explícita.
- Excluye las rutas de cachés de página y CDN.
- No registres cuerpos ni identificadores completos.

## Cifrado heredado

AutoScript 1.9.0, incluido en el tag oficial `v1.9.2`, usa DES con una clave
numérica corta en el modo de servidor intermedio. Versiones posteriores del
código oficial incorporan AES-CBC en contextos seguros y conservan DES como
compatibilidad.

La librería PHP no elige ni implementa ese cifrado: transporta texto opaco. El
proyecto consumidor debe fijar una versión de AutoScript compatible, documentar
su riesgo y probarla con las versiones móviles admitidas.

## Lo que esta librería no valida

Una respuesta recibida desde AutoFirma no debe considerarse válida por el mero
hecho de haber completado el intercambio. El sistema consumidor sigue siendo
responsable de validar la firma, el documento, el certificado, la cadena de
confianza y la revocación cuando proceda.
