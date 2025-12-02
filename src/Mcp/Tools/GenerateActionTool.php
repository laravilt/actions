<?php

namespace Laravilt\Actions\Mcp\Tools;

use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class GenerateActionTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = 'Generate a new action class with modal support, authorization, and URL integration';

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $name = $request->string('name');

        $command = 'php '.base_path('artisan').' make:action "'.$name.'" --no-interaction';

        if ($request->boolean('force', false)) {
            $command .= ' --force';
        }

        exec($command, $output, $returnCode);

        if ($returnCode === 0) {
            $response = "✅ Action '{$name}' created successfully!\n\n";
            $response .= "📖 Location: app/Actions/{$name}.php\n\n";
            $response .= "📦 Usage:\n";
            $response .= "```php\n";
            $response .= "use App\\Actions\\{$name};\n\n";
            $response .= "\$action = {$name}::make()\n";
            $response .= "    ->label('Action Label')\n";
            $response .= "    ->icon('icon-name')\n";
            $response .= "    ->color('primary')\n";
            $response .= "    ->requiresConfirmation()\n";
            $response .= "    ->action(function (\$record, \$data) {\n";
            $response .= "        // Action logic here\n";
            $response .= "    });\n";
            $response .= "```\n";

            return Response::text($response);
        } else {
            return Response::text('❌ Failed to create action: '.implode("\n", $output));
        }
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, \Illuminate\JsonSchema\JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()
                ->description('Action class name in StudlyCase (e.g., "ExportUserAction")')
                ->required(),
            'force' => $schema->boolean()
                ->description('Overwrite existing file')
                ->default(false),
        ];
    }
}
