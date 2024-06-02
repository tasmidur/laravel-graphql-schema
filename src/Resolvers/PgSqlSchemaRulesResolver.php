<?php

namespace Tasmidur\LaravelGraphqlSchema\Resolvers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tasmidur\LaravelGraphqlSchema\Contracts\SchemaRulesResolverInterface;
use stdClass;
use Tasmidur\LaravelGraphqlSchema\Helpers\ColumnRulesHelper;

class PgSqlSchemaRulesResolver extends SchemaRulesResolver implements SchemaRulesResolverInterface
{
    public static array $integerTypes = [
        'smallint' => [
            'unsigned' => ['0', '65535'],
            'signed' => ['-32768', '32767'],
        ],
        'integer' => [
            'unsigned' => ['0', '4294967295'],
            'signed' => ['-2147483648', '2147483647'],
        ],
        'bigint' => [
            'unsigned' => ['0', '18446744073709551615'],
            'signed' => ['-9223372036854775808', '9223372036854775807'],
        ]
    ];


    protected function getColumnsDefinitionsFromTable(): array
    {
        $tableName = $this->table();

        return collect(DB::select(
            '
            SELECT column_name, data_type, character_maximum_length, is_nullable, column_default
                FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name = :table ORDER BY ordinal_position',
            ['table' => $tableName]
        ))->keyBy('column_name')->toArray() ?? [];
    }

    protected function generateColumnRules(stdClass $column): array
    {
        $columnRules = [];
        $columnRules[] = $column->is_nullable === 'YES' ? 'nullable' : 'required';
        $type = Str::of($column->data_type)->lower();
        switch (true) {
            case ColumnRulesHelper::isBooleanType($type):
                $columnRules[] = 'boolean';
                break;

            case ColumnRulesHelper::isCharType($type):
                ColumnRulesHelper::addStringRules($columnRules, $type, $column->character_maximum_length);
                break;

            case ColumnRulesHelper::isTextType($type):
                ColumnRulesHelper::addTextRules($columnRules);
                break;

            case ColumnRulesHelper::isIntegerType($type):
                ColumnRulesHelper::addIntegerRules($columnRules, $type, self::$integerTypes);
                break;

            case ColumnRulesHelper::isNumericType($type):
                $columnRules[] = 'numeric';
                break;

            case ColumnRulesHelper::isEnumSetType($type):
                ColumnRulesHelper::addEnumSetRules($columnRules, $type);
                break;

            case ColumnRulesHelper::isYearType($type):
                ColumnRulesHelper::addYearRules($columnRules);
                break;

            case ColumnRulesHelper::isDateTimeType($type):
                $columnRules[] = 'date';
                break;

            case ColumnRulesHelper::isTimestampType($type):
                ColumnRulesHelper::addTimestampRules($columnRules);
                break;

            case ColumnRulesHelper::isJsonType($type):
                $columnRules[] = 'json';
                break;
        }
        return $columnRules;
    }

    protected function isAutoIncrement($column): bool
    {
        return Str::contains($column->column_default, 'nextval');
    }

    protected function getField($column): string
    {
        return $column->column_name;
    }
}
