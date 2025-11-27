<?php

namespace App\Helpers;

class UnidadConverter
{
    // Factores de conversión a gramos (para peso)
    private static $pesoAGramos = [
        'kg' => 1000,
        'g' => 1,
        'mg' => 0.001,
        'lb' => 453.592,
        'oz' => 28.3495,
        'arroba' => 11500, // 1 arroba = 11.5 kg = 11500 g
    ];

    // Factores de conversión a litros (para volumen)
    private static $volumenALitros = [
        'L' => 1,
        'mL' => 0.001,
        'gal' => 3.78541,
        'fl oz' => 0.0295735,
    ];

    /**
     * Convierte una cantidad de una unidad a otra
     * @param float $cantidad
     * @param string $unidadOrigen
     * @param string $unidadDestino
     * @return float
     */
    public static function convertir(float $cantidad, string $unidadOrigen, string $unidadDestino): float
    {
        // Si son la misma unidad, no hay conversión
        if ($unidadOrigen === $unidadDestino) {
            return $cantidad;
        }

        // Determinar si son unidades de peso o volumen
        $esPesoOrigen = isset(self::$pesoAGramos[$unidadOrigen]);
        $esPesoDestino = isset(self::$pesoAGramos[$unidadDestino]);
        $esVolumenOrigen = isset(self::$volumenALitros[$unidadOrigen]);
        $esVolumenDestino = isset(self::$volumenALitros[$unidadDestino]);

        // Si son del mismo tipo (ambas peso o ambas volumen)
        if (($esPesoOrigen && $esPesoDestino) || ($esVolumenOrigen && $esVolumenDestino)) {
            if ($esPesoOrigen && $esPesoDestino) {
                // Conversión de peso: convertir a gramos primero, luego a destino
                $enGramos = $cantidad * self::$pesoAGramos[$unidadOrigen];
                return $enGramos / self::$pesoAGramos[$unidadDestino];
            } else {
                // Conversión de volumen: convertir a litros primero, luego a destino
                $enLitros = $cantidad * self::$volumenALitros[$unidadOrigen];
                return $enLitros / self::$volumenALitros[$unidadDestino];
            }
        }

        // Si son de tipos diferentes, no se puede convertir
        throw new \InvalidArgumentException("No se puede convertir de {$unidadOrigen} a {$unidadDestino}. Deben ser del mismo tipo (peso o volumen).");
    }

    /**
     * Obtiene todas las unidades de peso disponibles
     */
    public static function getUnidadesPeso(): array
    {
        return array_keys(self::$pesoAGramos);
    }

    /**
     * Obtiene todas las unidades de volumen disponibles
     */
    public static function getUnidadesVolumen(): array
    {
        return array_keys(self::$volumenALitros);
    }

    /**
     * Determina si una unidad es de peso
     */
    public static function esPeso(string $unidad): bool
    {
        return isset(self::$pesoAGramos[$unidad]);
    }

    /**
     * Determina si una unidad es de volumen
     */
    public static function esVolumen(string $unidad): bool
    {
        return isset(self::$volumenALitros[$unidad]);
    }

    /**
     * Obtiene unidades compatibles con la unidad base del insumo
     */
    public static function getUnidadesCompatibles(string $unidadBase): array
    {
        if (self::esPeso($unidadBase)) {
            return self::getUnidadesPeso();
        } elseif (self::esVolumen($unidadBase)) {
            return self::getUnidadesVolumen();
        }
        // Si es una unidad (unid, pza, etc.), solo devolver esa unidad
        return [$unidadBase];
    }
}

