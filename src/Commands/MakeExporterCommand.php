<?php

namespace Laravilt\Actions\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeExporterCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravilt:exporter {name : The name of the exporter}
                            {--model= : The model class to export}
                            {--force : Overwrite existing file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new exporter class for ExportAction';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Exporter';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        parent::handle();

        $this->components->info("Exporter [{$this->argument('name')}] created successfully.");

        // Show usage example
        $this->newLine();
        $this->components->bulletList([
            'Import: use App\Exports\\'.str_replace('/', '\\', $this->argument('name')).';',
            'Usage: ExportAction::make()->exporter('.class_basename($this->argument('name')).'::class)',
        ]);
    }

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return __DIR__.'/../../stubs/exporter.stub';
    }

    /**
     * Get the default namespace for the class.
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\\Exports';
    }

    /**
     * Build the class with the given name.
     */
    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        $model = $this->option('model');

        if ($model) {
            $modelClass = class_basename($model);
            $modelNamespace = Str::startsWith($model, '\\') ? $model : 'App\\Models\\'.$model;
        } else {
            // Try to guess model from exporter name (e.g., UserExporter -> User)
            $exporterName = class_basename($this->argument('name'));
            $modelClass = Str::replaceLast('Exporter', '', $exporterName);
            $modelNamespace = 'App\\Models\\'.$modelClass;
        }

        $stub = str_replace('{{ model }}', $modelNamespace, $stub);
        $stub = str_replace('{{ modelClass }}', $modelClass, $stub);

        return $stub;
    }

    /**
     * Get the destination class path.
     */
    protected function getPath($name): string
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return $this->laravel['path'].'/'.str_replace('\\', '/', $name).'.php';
    }
}
