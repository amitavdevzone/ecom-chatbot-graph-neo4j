<?php

namespace App\Support;

use InvalidArgumentException;

class RunnableCypher
{
    /**
     * @param  array<string, mixed>  $parameters
     */
    public static function format(string $cypher, array $parameters): string
    {
        $names = array_keys($parameters);

        usort($names, fn (string $a, string $b): int => strlen($b) <=> strlen($a));

        $runnable = $cypher;

        foreach ($names as $name) {
            $runnable = str_replace(
                '$'.$name,
                self::literal($parameters[$name]),
                $runnable,
            );
        }

        return $runnable;
    }

    public static function literal(mixed $value): string
    {
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return 'null';
        }

        if (is_string($value)) {
            return "'".str_replace(['\\', "'"], ['\\\\', "\\'"], $value)."'";
        }

        throw new InvalidArgumentException('Unsupported Cypher parameter type: '.get_debug_type($value));
    }
}
