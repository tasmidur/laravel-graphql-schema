<?php

namespace Tasmidur\LaravelGraphqlSchema\Resolvers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tasmidur\LaravelGraphqlSchema\Contracts\SchemaRulesResolverInterface;
use stdClass;
use Tasmidur\LaravelGraphqlSchema\Helpers\ColumnRulesHelper;

class SqliteSchemaRulesResolver extends SchemaRulesResolver implements SchemaRulesResolverInterface
{
    protected function getColumnsDefinitionsFromTable(): array
    {

        return collect(DB::select("PRAGMA table_info('{$this->table()}')"))->keyBy('name')->toArray() ?? [];
    }

    protected function generateColumnRules(stdClass $column): array
    {
        $columnRules = [];
        $columnRules[] = $column->notnull ? 'required' : 'nullable';
        $type = Str::of($column->type)->lower();

        switch (true) {
            case ColumnRulesHelper::isBooleanType($type):
                $columnRules[] = 'boolean';
                break;

            case ColumnRulesHelper::isCharType($type):
                ColumnRulesHelper::addStringRules($columnRules, $type, 0);
                break;

            case ColumnRulesHelper::isTextType($type):
                ColumnRulesHelper::addTextRules($columnRules);
                break;

            case ColumnRulesHelper::isIntegerType($type):
                ColumnRulesHelper::addIntegerRules($columnRules, $type);
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

            default:
                //$columnRules[] = 'string';
                break;
        }

        return $columnRules;
    }

    protected function isAutoIncrement($column): bool
    {
        return $column->pk;
    }

    protected function getField($column): string
    {
        return $column->name;
    }
}
