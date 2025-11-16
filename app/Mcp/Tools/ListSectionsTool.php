<?php

namespace App\Mcp\Tools;

use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class ListSectionsTool extends Tool
{
    protected string $description = <<<'MARKDOWN'
        Lista todas las secciones principales del Plan Director DDGA.
        Retorna un índice jerárquico de la estructura del documento en formato TOON.
    MARKDOWN;

    public function handle(Request $request): Response
    {
        $filePath = storage_path('mcp/plan-director.toon');

        if (!file_exists($filePath)) {
            return Response::text('Error: El archivo plan-director.toon no existe.');
        }

        $content = file_get_contents($filePath);
        $lines = explode("\n", $content);
        $sections = [];

        foreach ($lines as $lineNumber => $line) {
            if (preg_match('/^(\s*)([a-zA-Z][\w\-]*):/', $line, $matches)) {
                $indent = strlen($matches[1]);
                $sectionName = $matches[2];

                $level = ($indent === 0) ? 0 : intdiv($indent, 2);

                $sections[] = sprintf(
                    "%s%s (línea %d)",
                    str_repeat('  ', $level),
                    $sectionName,
                    $lineNumber + 1
                );
            }
        }

        if (empty($sections)) {
            return Response::text('No se encontraron secciones en el plan director.');
        }

        return Response::text(
            "Estructura del Plan Director DDGA:\n\n" . implode("\n", $sections)
        );
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
