<?php

use App\Mcp\Servers\PlanDirectorServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::local('plan-director', PlanDirectorServer::class);

// Mcp::web('/mcp/demo', \App\Mcp\Servers\PublicServer::class);
