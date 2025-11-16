<?php

namespace App\Mcp\Resources;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Resource;

class PlanDirectorResource extends Resource
{
    protected string $uri = 'ddga://plan-director';

    protected string $mimeType = 'text/plain';

    protected string $description = <<<'MARKDOWN'
        Plan Director para la Creación del Departamento de Datos y Gobierno Abierto (DDGA).
        Contiene toda la documentación gubernamental del plan en formato TOON (Token-Oriented Object Notation).
    MARKDOWN;

    public function handle(Request $request): Response
    {
        $filePath = storage_path('mcp/plan-director.toon');

        if (!file_exists($filePath)) {
            return Response::text('Error: El archivo plan-director.toon no existe en storage/mcp/');
        }

        $content = file_get_contents($filePath);

        return Response::text($content);
    }
}
