<?php

namespace Tasmidur\LaravelGraphqlSchema\Services;

use Tasmidur\LaravelGraphqlSchema\Contracts\SchemaRulesResolverInterface;
use Tasmidur\LaravelGraphqlSchema\Exceptions\UnsupportedDbDriverException;
use Tasmidur\LaravelGraphqlSchema\Resolvers\MysqlSchemaRulesResolver;
use Tasmidur\LaravelGraphqlSchema\Resolvers\PgSqlSchemaRulesResolver;
use Tasmidur\LaravelGraphqlSchema\Resolvers\SqliteSchemaRulesResolver;

class ColumnRulesService
{
    protected SchemaRulesResolverInterface $resolver;

    /**
     * @throws UnsupportedDbDriverException
     */
    public function __construct(string $driver, $params)
    {
        $this->resolver = match ($driver) {
            'sqlite' => new SqliteSchemaRulesResolver(...array_values($params)),
            'mysql' => new MysqlSchemaRulesResolver(...array_values($params)),
            'pgsql' => new PgSqlSchemaRulesResolver(...array_values($params)),
            default => throw new UnsupportedDbDriverException('This db driver is not supported: ' . $driver),
        };
    }

    public function getRules(): array
    {
        return $this->resolver->generate();
    }
}
