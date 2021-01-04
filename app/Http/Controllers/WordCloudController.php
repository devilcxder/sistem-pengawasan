<?php

namespace App\Http\Controllers;

use App\Models\Tweet;
use Illuminate\Http\Request;
use Phpml\FeatureExtraction\TfIdfTransformer;
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Tokenization\WhitespaceTokenizer;

class WordCloudController extends Controller
{
    public function index()
    {
        $tweets = Tweet::select('tweet_prepro')->join('detail_tweets', 'tweets.id', '=', 'tweet_id')->where('label', '=', request()->emotion)->take(300)->get()->toArray();
        foreach ($tweets as $tweet) {
            $data[] = $tweet['tweet_prepro'];
        }

        $vectorizer = new TokenCountVectorizer(new WhitespaceTokenizer);

        // Build the dictionary.
        $vectorizer->fit($data);

        // Transform the provided text samples into a vectorized list.
        $vectorizer->transform($data);
        $words = $vectorizer->getVocabulary();

        $transformer = new TfIdfTransformer($data);
        $transformer->transform($data);
        $topKeyword = array();
        foreach ($data as $rows) {
            foreach ($rows as $key => $row) {
                array_key_exists($key, $topKeyword) ? $topKeyword[$key] += $row : $topKeyword[$key] = $row;
            }
        }
        foreach ($topKeyword as $key => $value) {
            $keyword_score[$key]['word'] = $words[$key];
            $keyword_score[$key]['score'] = $value;
        }

        //Sort higher to lower
        usort($keyword_score, function ($a, $b) {
            return $a['score'] <=> $b['score'];
        });

        $keyword_score = array_reverse($keyword_score);
        $sum = 0;
        for ($i = 0; $i < 20; $i++) {
            $result[] = [$keyword_score[$i]['word'], $keyword_score[$i]['score']];
            $sum += $keyword_score[$i]['score'];
        }

        foreach ($result as $key => $value) {
            $result[$key][1] = $result[$key][1] / $sum * 600;
        }
        return $result;
    }
}
