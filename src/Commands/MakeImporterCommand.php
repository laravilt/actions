<?php

namespace Laravilt\Actions\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeImporterCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravilt:importer {name : The name of the importer}
                            {--model= : The model class to import}
                            {--force : Overwrite existing file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new importer class for ImportAction';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Importer';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        parent::handle();

        $this->components->info("Importer [{$this->argument('name')}] created successfully.");

        // Show usage example
        $this->newLine();
        $this->components->bulletList([
            'Import: use App\Imports\\'.str_replace('/', '\\', $this->argument('name')).';',
            'Usage: ImportAction::make()->importer('.class_basename($this->argument('name')).'::class)',
        ]);
    }

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return __DIR__.'/../../stubs/importer.stub';
    }

    /**
     * Get the default namespace for the class.
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\\Imports';
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
            // Try to guess model from importer name (e.g., UserImporter -> User)
            $importerName = class_basename($this->argument('name'));
            $modelClass = Str::replaceLast('Importer', '', $importerName);
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
