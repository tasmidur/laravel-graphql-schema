<?php

namespace Tasmidur\LaravelGraphqlSchema\Services;

use Tasmidur\LaravelGraphqlSchema\Helpers\GraphQLHelper;

class GraphQLTypeParser
{
    public function parsedToGraphQLType(string $table, array $rules): array
    {
        $definition = ["validation_rules" => $rules];
        foreach ($rules as $key => $rule) {
            $isRequired = $rule[0] === 'required';
            $attributeType = $rule[1] ?? null;

            $definition["fields"][$key] = [
                "type" => GraphQLHelper::getGraphQLType($attributeType, $isRequired),
                "description" => "The $key of the $table"
            ];
            $definition["args"][] = [
                "name" => $key,
                "type" => GraphQLHelper::getGraphQLType($attributeType,false)
            ];
            $definition["args_with_validation"][] = [
                "name" => $key,
                "type" => GraphQLHelper::getGraphQLType($attributeType,$isRequired)
            ];
        }
        return [
            $definition['fields'],
            $definition['args'],
            $definition['validation_rules'],
            $definition["args_with_validation"]
        ];
    }
}
