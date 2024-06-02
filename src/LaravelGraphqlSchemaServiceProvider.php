<?php

namespace Tasmidur\LaravelGraphqlSchema;

use Illuminate\Support\ServiceProvider;
use Tasmidur\LaravelGraphqlSchema\Commands\GenerateSchemaCommand;
use Tasmidur\LaravelGraphqlSchema\Contracts\SchemaRulesResolverInterface;
use Tasmidur\LaravelGraphqlSchema\Exceptions\UnsupportedDbDriverException;
use Tasmidur\LaravelGraphqlSchema\Resolvers\MysqlSchemaRulesResolver;
use Tasmidur\LaravelGraphqlSchema\Resolvers\PgSqlSchemaRulesResolver;
use Tasmidur\LaravelGraphqlSchema\Resolvers\SqliteSchemaRulesResolver;
use Tasmidur\LaravelGraphqlSchema\Services\ColumnRulesService;

class LaravelGraphqlSchemaServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        parent::register();

        $this->mergeConfigFrom(
            __DIR__ . '/../config/graphql-schema-rules.php', 'graphql-schema-rules'
        );

        $this->app->singleton(ColumnRulesService::class, function ($app, $parameters) {
            $connection = config('database.default');
            $driver = config("database.connections.{$connection}.driver");
            return new ColumnRulesService($driver, $parameters);
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateSchemaCommand::class,
            ]);
        }

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        //php artisan vendor:publish --tag=courier-config
        $this->publishes([
            __DIR__ . '/../config/graphql-schema-rules.php' => config_path('graphql-schema-rules.php'),
        ], "graphql-schema-config");

    }


}
