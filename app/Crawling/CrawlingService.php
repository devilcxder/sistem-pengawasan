<?php

namespace App\Crawling;

use App\Models\DetailTweet;
use App\Models\DModel;
use App\Models\Tweet;
use App\Preprocessing\PreprocessingService;
use DateTime;
use DateTimeZone;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;
use Thujohn\Twitter\Facades\Twitter;

class CrawlingService
{
    public static function index($keyword, $count)
    {        
        //Last ID
        $last_id = Tweet::select('post_id')->orderBy('id', 'DESC')->first();
        if ($last_id == null) {
            $tweets = Twitter::getSearch(['q' => $keyword . ' -RT', 'tweet_mode' => 'extended', 'lang' => 'id', 'count' => $count, 'format' => 'array']);
        } else {
            $tweets = Twitter::getSearch(['q' => $keyword . ' -RT', 'tweet_mode' => 'extended', 'lang' => 'id', 'count' => $count, 'since_id' => intval($last_id['post_id']), 'format' => 'array']);
        }

        foreach (array_reverse($tweets['statuses']) as $key => $value) {
            //Change format date and Time Zone to Asia/Jakarta
            $dt = DateTime::createFromFormat('D M d H:i:s P Y', $value['created_at'])->setTimezone(new DateTimeZone('Asia/Jakarta'));

            $filter_tweet[$key]['post_id'] = $value['id_str'];
            $filter_tweet[$key]['username'] = $value['user']['name'];
            $filter_tweet[$key]['tweet'] = $value['full_text'];
            $filter_tweet[$key]['created_at'] = $dt->format('Y-m-d H:i:s');
            $full_text[] = $value['full_text'];
        }        
        
        $tweet_after_prepro = PreprocessingService::index($full_text);

        //Choose model
        $model = DModel::select('id','model_name')->orderBy('id', 'DESC')->first();        
        $estimator = PersistentModel::load(new Filesystem(storage_path() . '/model/'. $model->model_name .'.model'));        
        if (count($filter_tweet) === count($tweet_after_prepro)) {                        
            foreach($filter_tweet as $key => $tweet){                        
                $insert_tweet = new Tweet;
                $insert_prediction = new DetailTweet;
                $insert_tweet->d_model_id = $model->id;
                $insert_tweet->post_id = $tweet['post_id'];
                $insert_tweet->username = $tweet['username'];
                $insert_tweet->tweet = $tweet['tweet'];
                $insert_tweet->tweet_prepro = $tweet_after_prepro[$key];                
                $insert_tweet->save();
                
                // Prediction
                $emotion = $estimator->predictSample([$tweet_after_prepro[$key]]);                

                $insert_prediction->label = $emotion;
                $insert_prediction->created_at = $tweet['created_at'];
                $insert_tweet->detail_tweet()->save($insert_prediction);
            }            
        }
    }
}