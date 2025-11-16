<?php

namespace App\Mcp\Tools;

use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class SearchPlanTool extends Tool
{
    protected string $description = <<<'MARKDOWN'
        Busca texto dentro del Plan Director DDGA.
        Realiza una búsqueda case-insensitive del término proporcionado y retorna las líneas que coinciden con contexto.
    MARKDOWN;

    public function handle(Request $request): Response
    {
        $query = $request->input('query');
        $filePath = storage_path('mcp/plan-director.toon');

        if (!file_exists($filePath)) {
            return Response::text('Error: El archivo plan-director.toon no existe.');
        }

        $content = file_get_contents($filePath);
        $lines = explode("\n", $content);
        $results = [];

        foreach ($lines as $lineNumber => $line) {
            if (stripos($line, $query) !== false) {
                $results[] = sprintf(
                    "Línea %d: %s",
                    $lineNumber + 1,
                    trim($line)
                );
            }
        }

        if (empty($results)) {
            return Response::text("No se encontraron coincidencias para: '{$query}'");
        }

        return Response::text(
            "Resultados para '{$query}':\n\n" . implode("\n", $results)
        );
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()
                ->description('El texto a buscar dentro del plan director'),
        ];
    }
}
