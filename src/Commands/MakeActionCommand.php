<?php

namespace Laravilt\Actions\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeActionCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:action {name : The name of the action}
                            {--modal : Include modal confirmation}
                            {--form : Include form schema}
                            {--auth : Include authorization}
                            {--force : Overwrite existing file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new action class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Action';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        parent::handle();

        $this->components->info("Action [{$this->argument('name')}] created successfully.");

        // Show usage example
        $this->newLine();
        $this->components->bulletList([
            'Import: use App\Actions\\'.str_replace('/', '\\', $this->argument('name')).';',
            'Usage: '.class_basename($this->argument('name')).'::make()->execute()',
        ]);
    }

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        if ($this->option('form')) {
            return __DIR__.'/../../stubs/action.form.stub';
        }

        if ($this->option('modal')) {
            return __DIR__.'/../../stubs/action.modal.stub';
        }

        return __DIR__.'/../../stubs/action.stub';
    }

    /**
     * Get the default namespace for the class.
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\\Actions';
    }

    /**
     * Build the class with the given name.
     */
    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        return $this->replaceActionName($stub);
    }

    /**
     * Replace the action name in the stub.
     */
    protected function replaceActionName(string $stub): string
    {
        $name = class_basename($this->argument('name'));
        $label = Str::headline($name);

        $stub = str_replace('{{ actionName }}', $name, $stub);
        $stub = str_replace('{{ actionLabel }}', $label, $stub);

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
