<?php

namespace App\Support;

/**
 * Listas del formato de Excel (oficinas, tipos de contratación, servicios y categorías).
 * Se leen de database/data/listas_formato.json, que se genera a partir de la plantilla con
 * "php artisan listas:actualizar" para que los nombres sean EXACTAMENTE los del formato.
 */
class ListasFormato
{
    private static ?array $datos = null;

    private static function datos(): array
    {
        if (self::$datos === null) {
            $ruta = database_path('data/listas_formato.json');
            self::$datos = is_file($ruta) ? (json_decode(file_get_contents($ruta), true) ?: []) : [];
        }

        return self::$datos;
    }

    /** @return string[] */
    public static function oficinas(): array
    {
        return self::datos()['oficinas'] ?? [];
    }

    /** @return string[] */
    public static function tiposContratacion(): array
    {
        return self::datos()['tipos_contratacion'] ?? [];
    }

    /**
     * Mismo formato que usaba SolicitudController::getCatalogo():
     * clave => ['nombre' => ..., 'categorias' => [clave => nombre]]
     * Aquí la clave, el nombre y la categoría son los textos exactos del Excel.
     */
    public static function catalogo(): array
    {
        $catalogo = [];

        foreach (self::datos()['servicios'] ?? [] as $servicio => $categorias) {
            $catalogo[$servicio] = [
                'nombre' => $servicio,
                'categorias' => array_combine($categorias, $categorias),
            ];
        }

        return $catalogo;
    }

    /**
     * Categorías de ABC que incluyen cuenta de correo.
     * Se usan como requisito para pedir la licencia de Office 365 (ver LEEME: es una regla a confirmar).
     *
     * @return string[]
     */
    public static function categoriasConCorreo(): array
    {
        return ['AD-EX USUARIO Y CUENTA DE CORREO', 'EX CUENTA DE CORREO'];
    }

    /**
     * Servicios en los que solo se permite UNA solicitud por tipo (no varias categorias).
     * En los demas se puede pedir una solicitud por categoria.
     *
     * @return string[]
     */
    public static function serviciosUnaSolicitud(): array
    {
        return ['PERFILES_DE_ACCESO_A_INTERNET'];
    }

    /**
     * Categorias que se traslapan y no se pueden pedir juntas: la "doble" incluye a las otras dos.
     *
     * @return array<string, string[]>
     */
    public static function categoriasIncompatibles(): array
    {
        return [
            'AD-EX USUARIO Y CUENTA DE CORREO' => ['AD USUARIO', 'EX CUENTA DE CORREO'],
            'AD USUARIO' => ['AD-EX USUARIO Y CUENTA DE CORREO'],
            'EX CUENTA DE CORREO' => ['AD-EX USUARIO Y CUENTA DE CORREO'],
        ];
    }
}
