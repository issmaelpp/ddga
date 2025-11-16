<?php

namespace App\Mcp\Tools;

use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class GetSectionTool extends Tool
{
    protected string $description = <<<'MARKDOWN'
        Obtiene una sección específica del Plan Director DDGA.
        Busca la sección por su nombre/título y retorna todo su contenido (incluyendo subsecciones).
        Funciona tanto para secciones con contenido como para secciones que solo tienen título.
    MARKDOWN;

    public function handle(Request $request): Response
    {
        $sectionName = $request->input('section_name');
        $filePath = storage_path('mcp/plan-director.toon');

        if (!file_exists($filePath)) {
            return Response::text('Error: El archivo plan-director.toon no existe.');
        }

        $content = file_get_contents($filePath);
        $lines = explode("\n", $content);
        $capturing = false;
        $sectionContent = [];
        $indentLevel = null;

        foreach ($lines as $line) {
            if (stripos($line, $sectionName) !== false && preg_match('/^\s*[\w\-]+:/', $line)) {
                $capturing = true;
                $indentLevel = strlen($line) - strlen(ltrim($line));
                $sectionContent[] = $line;
                continue;
            }

            if ($capturing) {
                if (trim($line) === '') {
                    $sectionContent[] = $line;
                    continue;
                }

                $currentIndent = strlen($line) - strlen(ltrim($line));

                if ($currentIndent > $indentLevel) {
                    $sectionContent[] = $line;
                } else {
                    break;
                }
            }
        }

        if (empty($sectionContent)) {
            return Response::text("No se encontró la sección: '{$sectionName}'");
        }

        $hasContent = false;
        foreach ($sectionContent as $line) {
            if (preg_match('/contenido:\s*\|/', $line)) {
                $hasContent = true;
                break;
            }
        }

        $message = $hasContent
            ? "Contenido de la sección '{$sectionName}':\n\n"
            : "Sección '{$sectionName}' (sin contenido, solo estructura):\n\n";

        return Response::text($message . implode("\n", $sectionContent));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'section_name' => $schema->string()
                ->description('El nombre o título de la sección a obtener del plan director'),
        ];
    }
}
