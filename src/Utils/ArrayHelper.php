<?php

declare(strict_types=1);

namespace Flipsite\Utils;

use Ckr\Util\ArrayMerger;

final class ArrayHelper
{
    public static function getDot(array $ref, array $array)
    {
        if (1 === count($ref)) {
            return $array[array_shift($ref)] ?? null;
        }
        $next = array_shift($ref);
        if (isset($array[$next])) {
            return self::getDot($ref, $array[$next]);
        }
        return null;
    }

    public static function isAssociative(array $array) : bool
    {
        if ([] === $array) {
            return false;
        }
        return array_keys($array) !== range(0, count($array) - 1);
    }

    public static function renameKey(array $array, string $old, string $new) : array
    {
        $keys = array_keys($array);
        if (false === $index = array_search($old, $keys, true)) {
            throw new \Exception(sprintf('Key "%s" does not exist', $old));
        }
        $keys[$index] = $new;
        return array_combine($keys, array_values($array));
    }

    public static function merge(array ...$arrays) : array
    {
        $merged = [];
        while (count($arrays)) {
            $merged = ArrayMerger::doMerge($merged, array_shift($arrays), ArrayMerger::FLAG_OVERWRITE_NUMERIC_KEY);
        }
        return $merged;
    }

    public static function unDot(array $array, string $delimiter = '.') : array
    {
        $exploded       = [];
        $renameAndEmpty = [];

        foreach ($array as $attr => $val) {
            if (is_array($val)) {
                $val = self::unDot($val, $delimiter);
            }
            if (is_string($attr) && false !== mb_strpos($attr, $delimiter)) {
                $attrs   = explode($delimiter, $attr);
                $newAttr = array_shift($attrs);
                if (count($attrs)) {
                    $val = self::unDot([implode($delimiter, $attrs) => $val], $delimiter);
                }
                $val = is_array($val) ? self::unDot($val, $delimiter) : $val;
                if (isset($exploded[$newAttr])) {
                    $exploded[$newAttr] = self::merge($exploded[$newAttr], $val);
                    unset($array[$attr]);
                } else {
                    $exploded[$newAttr]    = $val;
                    $renameAndEmpty[$attr] = $newAttr;
                }
            } else {
                $array[$attr] = $val;
            }
        }
        if (count($exploded)) {
            foreach ($renameAndEmpty as $old => $new) {
                $existing    = $array[$new] ?? [];
                $array       = self::renameKey($array, $old, $new);
                $array[$new] = self::merge($existing, $exploded[$new]);
                unset($exploded[$new]);
            }
        }
        return $array;
    }
}
