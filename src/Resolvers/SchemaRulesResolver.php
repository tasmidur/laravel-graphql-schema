<?php

namespace Tasmidur\LaravelGraphqlSchema\Resolvers;

use Tasmidur\LaravelGraphqlSchema\Contracts\SchemaRulesResolverInterface;
use stdClass;

abstract class SchemaRulesResolver implements SchemaRulesResolverInterface
{
    private string $table;

    private array $columns;

    public function __construct(string $table, array $columns = [])
    {
        $this->table = $table;
        $this->columns = $columns;
    }

    public function generate(): array
    {
        $tableColumns = $this->getColumnsDefinitionsFromTable();

        $skip_columns = config('graphql-schema-rules.skip_columns');


        $tableRules = [];
        foreach ($tableColumns as $column) {
            $field = $this->getField($column);
            // If column should be skipped
            if (in_array($field, $skip_columns)) {
                continue;
            }
            $tableRules[$field] = $this->generateColumnRules($column);
        }

        return $tableRules;
    }

    protected function table(): string
    {
        return $this->table;
    }

    protected function columns(): array
    {
        return $this->columns;
    }

    abstract protected function isAutoIncrement($column): bool;

    abstract protected function getField($column): string;

    abstract protected function getColumnsDefinitionsFromTable(): array;

    abstract protected function generateColumnRules(stdClass $column): array;
}
