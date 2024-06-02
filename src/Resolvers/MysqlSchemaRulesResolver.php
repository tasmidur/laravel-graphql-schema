<?php

namespace Tasmidur\LaravelGraphqlSchema\Resolvers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tasmidur\LaravelGraphqlSchema\Contracts\SchemaRulesResolverInterface;
use stdClass;
use Tasmidur\LaravelGraphqlSchema\Helpers\ColumnRulesHelper;

class MysqlSchemaRulesResolver extends SchemaRulesResolver implements SchemaRulesResolverInterface
{
    public static array $integerTypes = [
        'tinyint' => [
            'unsigned' => ['0', '255'],
            'signed' => ['-128', '127'],
        ],
        'smallint' => [
            'unsigned' => ['0', '65535'],
            'signed' => ['-32768', '32767'],
        ],
        'mediumint' => [
            'unsigned' => ['0', '16777215'],
            'signed' => ['-8388608', '8388607'],
        ],
        'int' => [
            'unsigned' => ['0', '4294967295'],
            'signed' => ['-2147483648', '2147483647'],
        ],
        'bigint' => [
            'unsigned' => ['0', '18446744073709551615'],
            'signed' => ['-9223372036854775808', '9223372036854775807'],
        ],
    ];

    protected function getColumnsDefinitionsFromTable(): array
    {
        $tableName = $this->table();
        return collect(DB::select('SHOW COLUMNS FROM ' . $tableName))->keyBy('Field')->toArray() ?? [];
    }

    protected function generateColumnRules(stdClass $column): array
    {
        $columnRules = [];
        $columnRules[] = $column->Null === 'YES' ? 'nullable' : 'required';
        $type = Str::of($column->Type)->lower();
        switch (true) {
            case ColumnRulesHelper::isBooleanType($type):
                $columnRules[] = 'boolean';
                break;

            case ColumnRulesHelper::isCharType($type):
                ColumnRulesHelper::addStringRules($columnRules, $type, filter_var($type, FILTER_SANITIZE_NUMBER_INT));
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
        return $column->Extra === 'auto_increment';
    }

    protected function getField($column): string
    {
        return $column->Field;
    }
}
