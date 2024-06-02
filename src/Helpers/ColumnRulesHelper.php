<?php

namespace Tasmidur\LaravelGraphqlSchema\Helpers;

use Illuminate\Support\Str;

class ColumnRulesHelper
{
    public static function isBooleanType($type): bool
    {
        return $type == 'tinyint(1)' && config('graphql-schema-rules.tinyint1_to_bool') ||
            $type == 'boolean' || $type == 'bool';
    }

    public static function isCharType($type): bool
    {
        return Str::contains($type, 'char');
    }

    public static function isTextType($type): bool
    {
        return $type == 'text' || $type == 'clob';
    }

    public static function isIntegerType($type): bool
    {
        return Str::contains($type, 'int') || $type == 'serial' || $type == 'bigserial';
    }

    public static function isNumericType($type): bool
    {
        return Str::contains($type, 'double') || Str::contains($type, 'decimal') ||
            Str::contains($type, 'dec') || Str::contains($type, 'float') || Str::contains($type, 'numeric');
    }

    public static function isEnumSetType($type): bool
    {
        return Str::contains($type, 'enum') || Str::contains($type, 'set');
    }

    public static function isYearType($type): bool
    {
        return Str::contains($type, 'year');
    }

    public static function isDateTimeType($type): bool
    {
        return $type == 'date' || $type == 'time' || $type == 'datetime';
    }

    public static function isTimestampType($type): bool
    {
        return $type == 'timestamp';
    }

    public static function isJsonType($type): bool
    {
        return $type == 'json' || $type == 'jsonb';
    }

    public static function addStringRules(array &$columnRules, $type, $max): void
    {
        $columnRules[] = 'string';
        $columnRules[] = 'min:' . config('graphql-schema-rules.string_min_length');
        if ($max > 0) {
            $columnRules[] = 'max:' . $max;
        }
    }

    public static function addTextRules(array &$columnRules): void
    {
        $columnRules[] = 'string';
        $columnRules[] = 'min:' . config('graphql-schema-rules.string_min_length');
    }

    public static function addIntegerRules(array &$columnRules, $type, $integerTypes = null): void
    {

        $columnRules[] = 'integer';
        $sign = Str::contains($type, 'unsigned') ? 'unsigned' : 'signed';

        $intType = preg_replace("/\([^)]+\)/", '', Str::before($type, ' unsigned'));

        if ($integerTypes && (!empty($integerTypes[$intType][$sign][0] || $integerTypes[$intType][$sign][1]))) {
            $columnRules[] = 'min:' . $integerTypes[$intType][$sign][0];
            $columnRules[] = 'max:' . $integerTypes[$intType][$sign][1];
        }

    }

    public static function addEnumSetRules(array &$columnRules, $type): void
    {
        preg_match_all("/'([^']*)'/", $type, $matches);
        $columnRules[] = 'string';
        $columnRules[] = 'in:' . implode(',', $matches[1]);
    }

    public static function addYearRules(array &$columnRules): void
    {
        $columnRules[] = 'integer';
        $columnRules[] = 'min:1901';
        $columnRules[] = 'max:2155';
    }

    public static function addTimestampRules(array &$columnRules): void
    {
        $columnRules[] = 'date';
        $columnRules[] = 'after_or_equal:1970-01-01 00:00:01';
        $columnRules[] = 'before_or_equal:2038-01-19 03:14:07';
    }
}
