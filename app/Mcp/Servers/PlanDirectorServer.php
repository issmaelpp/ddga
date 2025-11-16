<?php

namespace App\Mcp\Servers;

use App\Mcp\Resources\PlanDirectorResource;
use App\Mcp\Tools\GetSectionTool;
use App\Mcp\Tools\ListSectionsTool;
use App\Mcp\Tools\SearchPlanTool;
use Laravel\Mcp\Server;

class PlanDirectorServer extends Server
{
    protected string $name = 'plan-director';

    protected string $version = '1.0.0';

    protected string $instructions = <<<'MARKDOWN'
        Este servidor MCP proporciona acceso al Plan Director para la Creación del Departamento de Datos y Gobierno Abierto (DDGA).

        El plan está almacenado en formato TOON (Token-Oriented Object Notation) para optimización de tokens.

        **Recursos disponibles:**
        - Plan Director completo en formato TOON (URI: ddga://plan-director)

        **Herramientas disponibles:**
        - search-plan: Busca texto dentro del plan
        - get-section: Obtiene una sección específica por nombre
        - list-sections: Lista todas las secciones del plan

        Usa estas herramientas para consultar y navegar el contenido del plan gubernamental de manera eficiente.
    MARKDOWN;

    protected array $tools = [
        SearchPlanTool::class,
        GetSectionTool::class,
        ListSectionsTool::class,
    ];

    protected array $resources = [
        PlanDirectorResource::class,
    ];

    protected array $prompts = [];
}
