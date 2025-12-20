<?php

declare(strict_types=1);

namespace Laravilt\Actions;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ExportAction extends Action
{
    protected ?string $exporterClass = null;

    protected ?string $fileName = null;

    protected string $writerType = \Maatwebsite\Excel\Excel::XLSX;

    protected ?Closure $queryModifier = null;

    protected array $columns = [];

    protected array $headings = [];

    protected ?string $disk = null;

    protected bool $queue = false;

    protected ?string $filePath = null;

    protected function setUp(): void
    {
        $this->name ??= 'export';
        $this->label(__('actions::actions.export.label'));
        $this->icon('download');
        $this->color('gray');
        $this->method('POST');
    }

    /**
     * Set the exporter class to use.
     *
     * @param  class-string  $exporter
     */
    public function exporter(string $exporter): static
    {
        $this->exporterClass = $exporter;

        return $this;
    }

    /**
     * Set the export filename.
     */
    public function fileName(string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Set the export format.
     */
    public function formats(array $formats): static
    {
        // For now, just use the first format
        if (! empty($formats)) {
            $this->writerType = $formats[0];
        }

        return $this;
    }

    /**
     * Set the writer type (xlsx, csv, etc).
     */
    public function writerType(string $type): static
    {
        $this->writerType = $type;

        return $this;
    }

    /**
     * Export as XLSX.
     */
    public function xlsx(): static
    {
        $this->writerType = \Maatwebsite\Excel\Excel::XLSX;

        return $this;
    }

    /**
     * Export as CSV.
     */
    public function csv(): static
    {
        $this->writerType = \Maatwebsite\Excel\Excel::CSV;

        return $this;
    }

    /**
     * Modify the query before exporting.
     */
    public function modifyQueryUsing(?Closure $callback): static
    {
        $this->queryModifier = $callback;

        return $this;
    }

    /**
     * Set the columns to export.
     */
    public function columns(array $columns): static
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Set the headings for the export.
     */
    public function headings(array $headings): static
    {
        $this->headings = $headings;

        return $this;
    }

    /**
     * Queue the export for background processing.
     */
    public function queue(bool $queue = true): static
    {
        $this->queue = $queue;

        return $this;
    }

    /**
     * Set the disk to store the export.
     */
    public function disk(string $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    /**
     * Set a custom file path for storing the export.
     */
    public function filePath(string $path): static
    {
        $this->filePath = $path;

        return $this;
    }

    /**
     * Get the exporter class.
     */
    public function getExporterClass(): ?string
    {
        return $this->exporterClass;
    }

    /**
     * Get the filename for the export.
     */
    public function getFileName(): string
    {
        if ($this->fileName) {
            return $this->fileName;
        }

        $extension = match ($this->writerType) {
            \Maatwebsite\Excel\Excel::CSV => 'csv',
            \Maatwebsite\Excel\Excel::TSV => 'tsv',
            \Maatwebsite\Excel\Excel::ODS => 'ods',
            \Maatwebsite\Excel\Excel::XLS => 'xls',
            \Maatwebsite\Excel\Excel::HTML => 'html',
            default => 'xlsx',
        };

        return 'export-'.Str::slug(now()->toDateTimeString()).'.'.$extension;
    }

    /**
     * Execute the export action.
     */
    public function execute(mixed $record = null, array $data = []): mixed
    {
        // If an exporter class is set, use it
        if ($this->exporterClass) {
            $exporter = new $this->exporterClass;

            if ($this->queue) {
                return Excel::queue($exporter, $this->getFileName(), $this->disk, $this->writerType);
            }

            return Excel::download($exporter, $this->getFileName(), $this->writerType);
        }

        // Otherwise, create a simple array export
        $collection = $this->getExportData($record);

        $export = new class($collection, $this->headings) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings
        {
            public function __construct(
                protected Collection $collection,
                protected array $headings
            ) {}

            public function collection(): Collection
            {
                return $this->collection;
            }

            public function headings(): array
            {
                return $this->headings;
            }
        };

        if ($this->queue) {
            return Excel::queue($export, $this->getFileName(), $this->disk, $this->writerType);
        }

        return Excel::download($export, $this->getFileName(), $this->writerType);
    }

    /**
     * Get the data to export.
     */
    protected function getExportData(mixed $record = null): Collection
    {
        if ($record instanceof Collection) {
            return $record;
        }

        if ($record instanceof Builder) {
            $query = $record;

            if ($this->queryModifier) {
                $query = call_user_func($this->queryModifier, $query);
            }

            return $query->get();
        }

        return collect([$record])->filter();
    }

    public function toArrayWithRecord(mixed $record = null): array
    {
        // Generate encrypted token for the export configuration
        $token = \Illuminate\Support\Facades\Crypt::encrypt([
            'exporter' => $this->exporterClass,
            'fileName' => $this->getFileName(),
        ]);

        // Set URL with token as query parameter - opens in new tab for download
        $this->url(route('actions.export', ['token' => $token]));
        $this->openUrlInNewTab(true);
        $this->method('GET');

        $data = parent::toArrayWithRecord($record);

        return $data;
    }
}
