<?php

namespace App\Imports;

use App\Models\Dataset;
use App\Preprocessing\PreprocessingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DatasetsImport implements ToModel, WithBatchInserts, WithHeadingRow, WithChunkReading, ShouldQueue, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsFailures, SkipsErrors;
    protected $category;

    public function __construct($category)
    {
        $this->category = $category;        
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {                       
        $prepro_train = PreprocessingService::index([$row['text']]);                 
        return new Dataset([
            'text' => $row['text'],
            'textPrepro' => $prepro_train[0],
            'label' => $row['label'],
            'category' => $this->category
        ]);
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