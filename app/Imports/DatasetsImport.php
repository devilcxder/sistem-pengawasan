<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Dataset;
use Illuminate\Support\Collection;
use App\Preprocessing\PreprocessingService;
use Illuminate\Auth\Events\Validated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class DatasetsImport implements ToModel, WithValidation, WithBatchInserts, WithHeadingRow, WithChunkReading, ShouldQueue, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsFailures, SkipsErrors;
    protected $category;

    public function __construct($category)
    {
        $this->category = Category::create(['category' => $category]);
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $prepro_train = PreprocessingService::index([$row['text']]);         

        $dataset = $this->category->datasets()->create([
            'text' => $row['text'],
            'textPrepro' => $prepro_train[0],
            'label' => $row['label']
        ]);
        return $dataset;
    }

    public function rules(): array
    {
        return [
            'text' => 'required|max:280',
            'label' => 'required|max:20'
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
