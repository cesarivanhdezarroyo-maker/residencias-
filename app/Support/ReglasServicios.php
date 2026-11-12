<?php

namespace App\Support;

/**
 * Reglas compartidas entre "Nueva solicitud" y "Mis servicios registrados" para saber
 * si un servicio ya se tiene (o se traslapa con otro) y no volver a pedirlo ni registrarlo.
 */
class ReglasServicios
{
    /**
     * Categorias de un servicio que impiden pedir o registrar $categoria:
     *  - ella misma (o todas las del servicio, si es de "una sola solicitud", como el perfil de internet);
     *  - las que se traslapan con ella (por ejemplo, la "doble" AD-EX incluye usuario y correo).
     *
     * @return string[]
     */
    public static function categoriasEnConflicto(string $servicio, string $categoria): array
    {
        $incompatibles = ListasFormato::categoriasIncompatibles()[$categoria] ?? [];

        if (in_array($servicio, ListasFormato::serviciosUnaSolicitud(), true)) {
            $todas = array_keys(ListasFormato::catalogo()[$servicio]['categorias'] ?? []);

            return array_values(array_unique(array_merge($todas, $incompatibles)));
        }

        return array_values(array_unique(array_merge([$categoria], $incompatibles)));
    }
}
