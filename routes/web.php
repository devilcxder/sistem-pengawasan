<?php

use App\Events\ChartEvent;
use App\Http\Controllers\ChartController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\WordCloudController;
use App\Models\Dataset;
use App\Preprocessing\PreprocessingService;
use Backpack\CRUD\app\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use Phpml\FeatureExtraction\TfIdfTransformer;
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Tokenization\WhitespaceTokenizer;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [AdminController::class, 'dashboard'])->name('backpack.dashboard');
Route::get('klasifikasi', [AdminController::class, 'classification'])->name('backpack.classification');
Route::post('classification-process', [AdminController::class, 'classification_process'])->name('classification.process');
Route::get('/admin', [AdminController::class, 'redirect'])->name('backpack');

Route::post('/chart/read-data', [ChartController::class, 'index'])->name('chart.read');
Route::post('/word-cloud', [WordCloudController::class, 'index'])->name('word.cloud');

Route::get('/prepro', function(){
    $keyword = ["WNI Telah Divaksin Covid-19, Phita Beberkan Kondisinya Usai Mendapatkan Vaksinasi di Inggris | tvOne https://t.co/RBZZ8t34DS"];
    $result = PreprocessingService::index($keyword);
    dd($result[0]['result']);
});

Route::get('/keyword', function () {
    $samples = Dataset::select("textPrepro")->take(10)->get()->toArray();
    foreach ($samples as $sample) {
        $data[] = $sample['textPrepro'];
    }

    $vectorizer = new TokenCountVectorizer(new WhitespaceTokenizer);

    // Build the dictionary.
    $vectorizer->fit($data);

    // Transform the provided text samples into a vectorized list.
    $vectorizer->transform($data);
    $word = $vectorizer->getVocabulary();

    // return $samples = [
    //    [0 => 1, 1 => 1, 2 => 2, 3 => 1, 4 => 1],
    //    [5 => 1, 6 => 1, 1 => 1, 2 => 1],
    //    [5 => 1, 7 => 2, 8 => 1, 9 => 1],
    //];
    $transformer = new TfIdfTransformer($data);
    $transformer->transform($data);
    $topKeyword = array();
    foreach ($data as $rows) {
        foreach ($rows as $key => $row) {
            array_key_exists($key, $topKeyword) ? $topKeyword[$key] += $row : $topKeyword[$key] = $row;
        }
    }
    $arr = array_combine($word, $topKeyword);
    arsort($arr);
    dd($arr);
});
