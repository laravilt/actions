<?php

declare(strict_types=1);

namespace Laravilt\Actions;

use Closure;
use Maatwebsite\Excel\Facades\Excel;

class ImportAction extends Action
{
    protected ?string $importerClass = null;

    protected string $readerType = \Maatwebsite\Excel\Excel::XLSX;

    protected ?Closure $beforeImport = null;

    protected ?Closure $afterImport = null;

    protected bool $queue = false;

    protected ?string $disk = null;

    protected array $chunkSize = [];

    protected array $acceptedFileTypes = [
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/csv',
    ];

    protected function setUp(): void
    {
        $this->name ??= 'import';
        $this->label(__('actions::actions.import.label'));
        $this->icon('upload');
        $this->color('gray');
        $this->requiresConfirmation();
        $this->modalHeading(__('actions::actions.import.modal.heading'));
        $this->modalDescription(__('actions::actions.import.modal.description'));
        $this->modalSubmitActionLabel(__('actions::actions.import.modal.submit'));
    }

    /**
     * Build the modal form schema dynamically to include the importer class.
     */
    public function getModalFormSchema(): array
    {
        return [
            \Laravilt\Forms\Components\Hidden::make('importer')
                ->default($this->importerClass),
            \Laravilt\Forms\Components\FileUpload::make('file')
                ->label(__('actions::actions.import.fields.file'))
                ->required()
                ->acceptedFileTypes($this->acceptedFileTypes),
        ];
    }

    /**
     * Set the importer class to use.
     *
     * @param  class-string  $importer
     */
    public function importer(string $importer): static
    {
        $this->importerClass = $importer;

        return $this;
    }

    /**
     * Set the reader type (xlsx, csv, etc).
     */
    public function readerType(string $type): static
    {
        $this->readerType = $type;

        return $this;
    }

    /**
     * Import from XLSX.
     */
    public function xlsx(): static
    {
        $this->readerType = \Maatwebsite\Excel\Excel::XLSX;

        return $this;
    }

    /**
     * Import from CSV.
     */
    public function csv(): static
    {
        $this->readerType = \Maatwebsite\Excel\Excel::CSV;

        return $this;
    }

    /**
     * Set accepted file types for upload.
     */
    public function acceptedFileTypes(array $types): static
    {
        $this->acceptedFileTypes = $types;

        return $this;
    }

    /**
     * Queue the import for background processing.
     */
    public function queue(bool $queue = true): static
    {
        $this->queue = $queue;

        return $this;
    }

    /**
     * Set the disk to read from.
     */
    public function disk(string $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    /**
     * Set the chunk size for importing.
     */
    public function chunkSize(int $size): static
    {
        $this->chunkSize = ['chunkSize' => $size];

        return $this;
    }

    /**
     * Callback to run before importing.
     */
    public function beforeImport(?Closure $callback): static
    {
        $this->beforeImport = $callback;

        return $this;
    }

    /**
     * Callback to run after importing.
     */
    public function afterImport(?Closure $callback): static
    {
        $this->afterImport = $callback;

        return $this;
    }

    /**
     * Get the importer class.
     */
    public function getImporterClass(): ?string
    {
        return $this->importerClass;
    }

    /**
     * Execute the import action.
     */
    public function execute(mixed $record = null, array $data = []): mixed
    {
        $file = $data['file'] ?? null;

        if (! $file) {
            throw new \InvalidArgumentException('No file provided for import.');
        }

        // Run before callback
        if ($this->beforeImport) {
            call_user_func($this->beforeImport, $file);
        }

        // If an importer class is set, use it
        if ($this->importerClass) {
            $importer = new $this->importerClass;

            if ($this->queue) {
                Excel::queueImport($importer, $file, $this->disk, $this->readerType);
            } else {
                Excel::import($importer, $file, $this->disk, $this->readerType);
            }
        }

        // Run after callback
        if ($this->afterImport) {
            call_user_func($this->afterImport, $file);
        }

        return true;
    }

    public function toArrayWithRecord(mixed $record = null): array
    {
        // Set URL dynamically (routes are available at this point)
        $this->url(route('actions.import'));

        // Set the modal form schema dynamically (includes hidden importer field)
        $this->schema($this->getModalFormSchema());

        $data = parent::toArrayWithRecord($record);

        // Include importer data that will be sent with the request (backup)
        $data['data'] = [
            'importer' => $this->importerClass,
        ];
        $data['acceptedFileTypes'] = $this->acceptedFileTypes;
        $data['hasAction'] = true; // Mark as having an action so frontend executes it

        return $data;
    }
}
