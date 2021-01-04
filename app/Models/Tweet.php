<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tweet extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    public $timestamps = false;
    
    public function detail_tweet()
    {
        return $this->hasOne(DetailTweet::class);
    }    

    public function tweet_created()
    {
        return $this->hasOne(DetailTweet::class);
    }    
}
