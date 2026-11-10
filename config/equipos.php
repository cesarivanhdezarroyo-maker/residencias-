<?php

/**
 * Integración con el registro de equipos (IP y hostname por número de empleado).
 * Apagada por defecto: no hace nada hasta que EQUIPOS_ENABLED=true en el .env.
 */
return [
    'enabled' => env('EQUIPOS_ENABLED', false),

    // "db" = lectura directa (opción A) | "api" = consulta HTTP (opción B)
    'driver' => env('EQUIPOS_DRIVER', 'db'),

    'db' => [
        'conexion' => 'equipos', // definida en config/database.php
        'tabla' => env('EQUIPOS_TABLA', 'equipos'),
        'columnas' => [
            'numero' => env('EQUIPOS_COL_NUMERO', 'numero_empleado'),
            'ip' => env('EQUIPOS_COL_IP', 'ip'),
            'hostname' => env('EQUIPOS_COL_HOSTNAME', 'hostname'),
            'activo' => env('EQUIPOS_COL_ACTIVO', null), // opcional: columna 1/0
            'tipo' => env('EQUIPOS_COL_TIPO', null),     // opcional: columna con el tipo de equipo
        ],
        // Si el empleado tiene varios equipos y la vista trae el tipo, se usa solo el de este tipo
        // (por ejemplo "computadora"). Se compara con LIKE, sin importar mayúsculas.
        'tipo_valor' => env('EQUIPOS_TIPO_VALOR', null),
    ],

    'api' => [
        'url' => env('EQUIPOS_API_URL'),
        'token' => env('EQUIPOS_API_TOKEN'),
        'timeout' => env('EQUIPOS_API_TIMEOUT', 3),
    ],
];
