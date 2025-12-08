<?php

namespace BinanceAPI\Helpers;

/**
 * Funções auxiliares para arrays
 */
class ArrayHelper
{
    /**
     * Obtém um valor de array usando notação de ponto
     *
     * @param array<string,mixed> $array Array fonte
     * @param string $key Chave em notação de ponto (ex: 'user.name')
     * @param mixed $default Valor padrão
     * @return mixed
     */
    public static function get(array $array, string $key, $default = null)
    {
        if (isset($array[$key])) {
            return $array[$key];
        }

        $keys = explode('.', $key);
        $current = $array;

        foreach ($keys as $k) {
            if (!is_array($current) || !array_key_exists($k, $current)) {
                return $default;
            }
            $current = $current[$k];
        }

        return $current;
    }

    /**
     * Define um valor usando notação de ponto
     *
     * @param array<string,mixed> $array Array a modificar
     * @param string $key Chave em notação de ponto
     * @param mixed $value Valor a definir
     * @return array<string,mixed>
     */
    public static function set(array &$array, string $key, $value): array
    {
        $keys = explode('.', $key);
        $current = &$array;

        foreach ($keys as $k) {
            if (!isset($current[$k]) || !is_array($current[$k])) {
                $current[$k] = [];
            }
            $current = &$current[$k];
        }

        $current = $value;
        return $array;
    }

    /**
     * Filtra array mantendo apenas chaves especificadas
     *
     * @param array<string,mixed> $array Array fonte
     * @param array<string> $keys Chaves a manter
     * @return array<string,mixed>
     */
    public static function only(array $array, array $keys): array
    {
        return array_intersect_key($array, array_flip($keys));
    }

    /**
     * Filtra array removendo chaves especificadas
     *
     * @param array<string,mixed> $array Array fonte
     * @param array<string> $keys Chaves a remover
     * @return array<string,mixed>
     */
    public static function except(array $array, array $keys): array
    {
        return array_diff_key($array, array_flip($keys));
    }

    /**
     * Verifica se todas as chaves existem no array
     *
     * @param array<string,mixed> $array Array a verificar
     * @param array<string> $keys Chaves requeridas
     */
    public static function hasAll(array $array, array $keys): bool
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $array)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Retorna as chaves que estão faltando no array
     *
     * @param array<string,mixed> $array Array a verificar
     * @param array<string> $keys Chaves requeridas
     * @return array<string>
     */
    public static function missing(array $array, array $keys): array
    {
        return array_values(array_diff($keys, array_keys($array)));
    }

    /**
     * Converte array para query string
     *
     * @param array<string,mixed> $array Array a converter
     */
    public static function toQueryString(array $array): string
    {
        return http_build_query($array);
    }

    /**
     * Aplana um array multidimensional
     *
     * @param array<mixed> $array Array a aplanar
     * @return array<mixed>
     */
    public static function flatten(array $array): array
    {
        $result = [];

        foreach ($array as $value) {
            if (is_array($value)) {
                $result = array_merge($result, self::flatten($value));
            } else {
                $result[] = $value;
            }
        }

        return $result;
    }
}
