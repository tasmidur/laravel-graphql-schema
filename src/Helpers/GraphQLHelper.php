<?php

namespace Tasmidur\LaravelGraphqlSchema\Helpers;

use Brick\VarExporter\ExportException;
use Brick\VarExporter\VarExporter;
use Illuminate\Support\Pluralizer;

class GraphQLHelper
{
    public static string $facadeClone = "FACADE_DOUBLE_CLONE";
    public static array $patternValidation = ['": ', '{', '}'];
    public static array $replacerValidation = ['"=>', "[", "]"];

    /**
     * @throws ExportException
     */
    public static function format(array $data): string
    {
        return VarExporter::export($data, VarExporter::INLINE_SCALAR_LIST);
    }

    public static function formatForFile(array $data, $pattens = null, $replacers = null): string
    {
        return str_replace($pattens ?? ['"#', '#"', '{', '}', ':', self::$facadeClone], $replacers ?? ['', '', '[', ']', '=>', '::'], json_encode($data, JSON_PRETTY_PRINT));
    }


    public static function getStubContents(array $stubVariables, string $stubFile): string
    {


        $contents = file_get_contents(__DIR__ . "/../../stubs/$stubFile.stub");

        foreach ($stubVariables as $search => $replace) {
            $contents = str_replace('$' . $search . '$', $replace, $contents);
        }

        return $contents;
    }

    public static function getGraphQLType(mixed $attributeType, bool $isRequired): string
    {
        $typeMapping = [
            "integer" => "int()",
            "numeric" => "float()",
            "boolean" => "boolean()",
            "default" => "string()",
        ];

        $type = $typeMapping[$attributeType] ?? $typeMapping['default'];
        return $isRequired ? "#Type" . self::$facadeClone . "nonNull(Type" . self::$facadeClone . "$type)#" : "#Type" . self::$facadeClone . "$type#";
    }

    public static function getSingularClassName(string $name): string
    {
        return ucwords(Pluralizer::singular($name));
    }
}
