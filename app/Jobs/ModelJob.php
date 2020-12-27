<?php

namespace App\Jobs;

use App\Models\Dataset;
use App\Models\DModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Rubix\ML\Classifiers\GaussianNB;
use Rubix\ML\CrossValidation\Reports\AggregateReport;
use Rubix\ML\CrossValidation\Reports\ConfusionMatrix;
use Rubix\ML\CrossValidation\Reports\MulticlassBreakdown;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Other\Tokenizers\NGram;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\Pipeline;
use Rubix\ML\Transformers\TfIdfTransformer;
use Rubix\ML\Transformers\WordCountVectorizer;

class ModelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $samples, $labels, $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->samples = [];
        $this->labels = [];
        $this->data = $data;  
        
        //Convert data split to float
        $this->data['data_split'] = $this->data['data_split']/100;                        
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = Dataset::select('textPrepro', 'label')->take(50)->get();
        foreach ($data as $train) {
            $this->samples[] = [$train->textPrepro];
            $this->labels[] = $train->label;
        }
        $dataset = Labeled::build($this->samples, $this->labels);
        [$training, $testing] = $dataset->stratifiedSplit($this->data['data_split']);
        $estimator = new PersistentModel(
            new Pipeline([
                new WordCountVectorizer(10000, 3, 10000, new NGram(1, 2)),
                new TfIdfTransformer(),
            ], new GaussianNB()),
            new Filesystem(storage_path() . '/model/' . $this->data['model_name'] . '.model', true)
        );

        $estimator->train($training);        

        $predictions = $estimator->predict($testing);

        //Report    
        $report = new AggregateReport([
            new MulticlassBreakdown(),
            new ConfusionMatrix(),
        ]);
        $results = $report->generate($predictions, $testing->labels());
        $estimator->save();

        //Save to DB
        $fix_model = DModel::create([
            'category_id' => $this->data['category_id'],
            'model_name' => $this->data['model_name'],
            'model_desc' => $this->data['model_desc'],
            'data_split' => $this->data['data_split'],
            'accuracy' => $results[0]['overall']['accuracy']
        ]);
    }
}