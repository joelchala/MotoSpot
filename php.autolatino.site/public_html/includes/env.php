<?php
/**
 * MotoSpot - Cargador de variables de entorno (.env)
 * Sin dependencias externas (reemplaza vlucas/phpdotenv para entornos sin Composer)
 */

/**
 * Carga el archivo .env y registra las variables en getenv() / $_ENV
 *
 * Rutas evaluadas en orden (usando paths RELATIVOS para portabilidad):
 *   __DIR__ = .../public_html/includes
 *   dirname(__DIR__, 2) = raíz del dominio (donde está .motospot/)
 *
 *   1. raíz-dominio/.motospot/.env   ← fuera de public_html (ideal)
 *   2. raíz-dominio/.env             ← un nivel arriba de public_html
 *   3. public_html/.env              ← último recurso
 */
function loadEnv(): void
{
    $domainRoot = dirname(__DIR__, 2); // sube: includes → public_html → raíz dominio

    $candidates = [
        $domainRoot . '/.motospot/.env',  // /php.autolatino.site/.motospot/.env
        $domainRoot . '/.env',            // /php.autolatino.site/.env
        dirname(__DIR__) . '/.env',       // /public_html/.env
    ];

    foreach ($candidates as $path) {
        if (is_readable($path)) {
            parseEnvFile($path);
            return;
        }
    }

    error_log('[MotoSpot] ADVERTENCIA: archivo .env no encontrado en ninguna ruta candidata.');
}

/**
 * Parsea el archivo .env línea por línea
 * Soporta: KEY=VALUE, KEY="VALUE", KEY='VALUE', # comentarios, líneas vacías
 */
function parseEnvFile(string $path): void
{
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) return;

    foreach ($lines as $line) {
        $line = trim($line);

        // Ignorar comentarios y líneas vacías
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        // Separar KEY=VALUE (solo en el primer =)
        $eqPos = strpos($line, '=');
        if ($eqPos === false) continue;

        $key   = trim(substr($line, 0, $eqPos));
        $value = trim(substr($line, $eqPos + 1));

        // Quitar comillas dobles o simples al valor
        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        // No sobreescribir variables ya definidas (permite override por entorno real)
        if (!array_key_exists($key, $_ENV) && getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key]    = $value;
            $_SERVER[$key] = $value;
        }
    }
}

/**
 * Obtiene una variable de entorno con valor por defecto
 */
function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? getenv($key);
    if ($value === false || $value === null) {
        return $default;
    }

    // Convertir strings booleanos
    return match(strtolower((string)$value)) {
        'true',  '(true)'  => true,
        'false', '(false)' => false,
        'null',  '(null)'  => null,
        'empty', '(empty)' => '',
        default            => $value,
    };
}
