<?php

namespace Laravilt\Actions\Mcp;

use Laravel\Mcp\Server;
use Laravilt\Actions\Mcp\Tools\GenerateActionTool;
use Laravilt\Actions\Mcp\Tools\SearchDocsTool;

class LaraviltActionsServer extends Server
{
    /**
     * The MCP server's name.
     */
    protected string $name = 'Laravilt Actions';

    /**
     * The MCP server's version.
     */
    protected string $version = '1.0.0';

    /**
     * The MCP server's instructions for the LLM.
     */
    protected string $instructions = <<<'MARKDOWN'
        This server provides action management capabilities for Laravilt projects.

        You can:
        - Generate new action classes with modal support
        - Search actions documentation
        - Access information about action features (modals, authorization, variants)

        Actions provide interactive UI components with authorization, modals, and URL integration.
    MARKDOWN;

    /**
     * The tools registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Tool>>
     */
    protected array $tools = [
        GenerateActionTool::class,
        SearchDocsTool::class,
    ];
}
